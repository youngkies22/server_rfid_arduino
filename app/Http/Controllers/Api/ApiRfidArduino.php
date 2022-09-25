<?php

//membaca lokasi Controller dalam Folder
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Arr;
use DB;
use App\SendRespon\BudutResponse;
use App\Models\User_siswa;

class ApiRfidArduino extends Controller
{
	public function __construct()
	{
		$this->pesan = 'ABSENSI ANDA';
		$this->pesan2 = 'TELAH BERHASIL';

		$this->pesanGagal = 'GAGAL';
		$this->pesanGagal2 = 'ABSENSI ANDA';
	}

	public function getUuId()
	{
		//return Str::uuid4()->toString();
		return Str::orderedUuid()->toString();
	}

	public function getJsonBudutwj()
	{
		//AsCgWnTkqnImWcPwQdWF1pEMQrpaJXhb token mesin
		$url = env('URL_SERVER_SINKRON_DATA_USER');
		$response = Http::get($url);
		if ($response->successful()) {
			$dataJson = $response->json();
			$dataArray = [];
			foreach ($dataJson['data'] as $val) {
				$data = [
					'ssaUsername'			=> $val['username'],
					'ssaSklId'				=> $val['sekolah'],
					'ssaJrsId'				=> $val['jurusan'],
					'ssaRblId'				=> $val['rombel'],
					'ssaTahunAngkata'	=> $val['tahun_angkatan'],
					'ssaLastName'			=> $val['nama_depan'],
					'ssaLastName'			=> $val['nama_belakang'],
					'ssaFullName'			=> $val['full_nama'],
					'ssaidKartuRfid'	=> $val['idkartu_rfid'],

				];
				$dataArray[] = $data;
			}
			if (!empty($dataArray)) {
				$dataArray = collect($dataArray); // Make a collection to use the chunk method
				$chunks = $dataArray->chunk(500); //pesah data per 500 untuk di proses

				foreach ($chunks as $chunk) {

					$array = $chunk->toArray();
					foreach ($array as $val) {
						$cek = DB::table('user_siswa')->where('ssaUsername', $val['ssaUsername'])->exists();
						if ($cek) {
							DB::table('user_siswa')->where('ssaUsername', $val['ssaUsername'])->update($val);
						} else {
							DB::table('user_siswa')->insert($val);
						}
					}
				}

				return BudutResponse::accept();
			} else {
				return BudutResponse::forbidden();
			}
		} //end if $response->successful
		else{
			return BudutResponse::badRequest();
		}

	}


	

//ARDUINO RFID -----------------------------------------------------------------------------------------------------------------------
	//function cek status scan masuk atau pulang ------------------------------------------------
	
	//function cek status scan masuk atau pulang ------------------------------------------------
	function cekAbsenDiDatabase($username, $tgl)
	{
		$db = DB::table('absen_finger_siswa')
			->where('afsSsaUsername', $username)
			->where('afsDatetime', $tgl);
		return $db;
	}
	/**
	 * cek batas scan absen siswa
	 * $jamSekarang @ param date("H:i")
	 * return @param int
	 */
	function cekBatasScan($jamSekarang){
		// $mulaiScanMasuk 	= "05.30"; $mulaiScanPulang 	= "13.00";
		// $batasScanMasuk 	= "10.00"; $batasScanPulang		= "18.00";

		$mulaiScanMasuk 	= env("MULAI_SCAN_MASUK"); $mulaiScanPulang 	= env("MULAI_SCAN_PULANG");
		$batasScanMasuk 	= env("BATAS_SCAN_MASUK"); $batasScanPulang		= env("BATAS_SCAN_PULANG");

		if(strtotime($jamSekarang) >= strtotime($mulaiScanMasuk) AND strtotime($jamSekarang) <= strtotime($batasScanMasuk)){
			return 1;
		}
		elseif(strtotime($jamSekarang) >= strtotime($mulaiScanPulang) AND strtotime($jamSekarang) <= strtotime($batasScanPulang)){
			return 2;
		}
		else{
			return 0;
		}

	}
	/**
	 * cek status jam in saat melakuakn absen pulang
	 */
	function cekStatusAbsenBerdasrkanJamIn($jamIn,$jamBatasMasuk,$jenis){
		
		if(strtotime($jamIn) <= strtotime($jamBatasMasuk)){
			#jika $jenis 1 maka status h kecil dan absen pulang kosong
			if($jenis == 1){
				return "h";
			}
			else{ 
				return "H"; #jika status absen pulang tidak kosong maka H besar
			}
			
		}
		elseif(strtotime($jamIn) >= strtotime($jamBatasMasuk)){
			return "T";
		}
		else{
			return "A";
		}
	}
	/**
	 * insert absensi ke databasse
	 * @param array $data
	 * @param string $table
	 */
	function dbInsert($table,$data,$status){
		
		try {

			DB::table($table)->insert($data);
			return BudutResponse::successAbsensBerhasil($status);
		} catch (\Exception $e) {
			return BudutResponse::ErrorAbsensiTambah();
		}
		
	}
	/**
	 * update absensi ke databasse
	 * @param array $data
	 * @param array $where
	 * @param string $table
	 */
	function dbUpdate($table,$where,$data,$status){
		
		try {

			DB::table($table)->where($where)->update($data);

			return BudutResponse::successAbsensBerhasil($status);

		} catch (\Exception $e) 
		{	
			//dd($e);
			return BudutResponse::ErrorAbsensiEdit();

		}
		
	}
	/**
	 * cek absensi sudah ada pada database
	 */
	function AndasudahScan(){
		return BudutResponse::successAndaSudahAbsen();
	}

//END ARDUINO RFID -----------------------------------------------------------------------------------------------------------------------


	/**
	 * menerima send post data dari arduino
	 * url utama dari proses arduino send data ke web server
	 * $idkartu uui scan kartu rfid @varchar
	 */
	function getArduino($idkartu)
	{

		//proses cache akun user rfid -------------------------------------------------------------------		
			if (Cache::has('user_siswa_'.$idkartu)) {
				$user = Cache::get('user_siswa_'.$idkartu);
			}
			else{
				
				$user = User_siswa::firstWhere('ssaidKartuRfid', $idkartu);
				//$user = User_siswa::get()->toJson();
				Cache::forever('user_siswa_'.$idkartu, $user);
			}
		//proses cache akun user rfid -------------------------------------------------------------------


		if ($user === null) {
			return BudutResponse::ErrorNotKartu();
		} else {
			/**
			 * catatan
			 * afsJenisAbsen = 1 sudah scan masuk scan pulang kosong, 2 sudah scan pulang scan masuk kosong , 3 sudah scan masuk dan pulang
			 * $jensiAbsen = 1 mesin finger, 2 guru, 3 siswa, 4 absen rfid
			 * 
			 * absen siswa mulai jam 06.00 sampai jam  08.00
			 * jika lebih dari jam 8.00 maka di tolak
			 * sampai jam 13.00 absen pulang sudah di mulai, sampai jam 18.00
			 * lebih dari 18.00 di tolak
			*/
			$jensiAbsen = 4; // 4 kode absen pakai rfid
			$namahari = date('l');
			$bulan = date('n');

			#stap 1
			if ($namahari == "Sunday" or $namahari == "Saturday") {
				return BudutResponse::ErrorHariLibur();
			}  
			else { //selain hari sabtu dan minggu //maka prosesss
				// $arrayPulangUpdate=[];
				// $arrayPulangInsert=[];
				
				#uncuk cek scan Status Hadir Terlambat
				$jamMasukSekolah 	= env("JAM_MASUK_SEKOLAH");
				$jamPulangSekolah	= env("JAM_PULANG_SEKOLAH");
				$jamBatasMasukSekolah		= env("JAM_BATAS_MASUK");

				$jamsekarang = date("H:i");
				$namahari = date('l', strtotime(date('Y-m-d')));
				$tglSekarang = date('Y-m-d');
				
				//dari db -----------------------------------
				$username = $user->ssaUsername;
				$sklId 		= $user->ssaSklId;
				$rblId 		= $user->ssaRblId;
				

				#cek batas scan absensi
				$statusJamScan = $this->cekBatasScan($jamsekarang);


				#stap 2
				if ($statusJamScan == 1) { // 1 jika scan masuk
					$cek = $this->cekAbsenDiDatabase($username, $tglSekarang); //cek apakah sudah ada absen di database
					
					if($cek->exists()){ //jika data absen sudah ada
						return $this->AndasudahScan();
					}
					else{ //jika ada absen belum ada
						$uuid	=	$this->getUuId();
						$cekStatusJamIn = $this->cekStatusAbsenBerdasrkanJamIn($jamsekarang,$jamBatasMasukSekolah,1);
						$arrayPulangInsert=[
							'afsId'						=>$uuid,
							'afsSklId'				=>$sklId,
							'afsSsaUsername'	=>$username,
							'afsRblId'				=>$rblId,
							'afsAkId'					=>$cekStatusJamIn,
							'afsDatetime'			=>$tglSekarang,
							'afsIn'						=>$jamsekarang,
							'afsOut'					=>null,
							'afsHari'					=>$namahari,
							'afsJenis'				=>$jensiAbsen,
							'afsJenisAbsen'		=>1
						];
						return $this->dbInsert("absen_finger_siswa",$arrayPulangInsert,1);
					}
				} 
				elseif ($statusJamScan == 2) { // 2 jika scan pulang
					
					$cek = $this->cekAbsenDiDatabase($username, $tglSekarang); //cek apakah sudah ada absen di database
					
					if($cek->exists()){
						#cek jam in pada absen database karna data ada, apakah null atau ada valuenya
						$cekJam = $cek->first();
						$cekJamIn = !empty($cekJam->afsIn) ? $cekJam->afsIn : null;
						//$cekJamOut = !empty($cekJam->afsOut) ? $cekJam->afsOut : null;
						
						//if($cekJamIn == null AND $cekJamOut == null){
						if($cekJamIn == null){
							$where = [
								'afsSsaUsername'	=>$username,
							];
							$arrayPulangUpdate=[
								'afsAkId'					=>"T",
								'afsIn'						=>null,
								'afsOut'					=>$jamsekarang,	
								'afsJenisAbsen'		=>2
							];
							
							return $this->dbUpdate("absen_finger_siswa",$where,$arrayPulangUpdate,2);
						}
						// elseif($cekJamIn == null AND $cekJamOut != null){
						// 	#jika scan masuk sudah ada, agar tidak terjadi scan berulang
						// 	return $this->AndasudahScan();
						// }
						else{
							#jika absen masuk sudah ada value nya dan absen pulang belum ada valuenya
							
							$cekStatusJamIn = $this->cekStatusAbsenBerdasrkanJamIn($cekJamIn,$jamBatasMasukSekolah,2);
							$where = [
								'afsSsaUsername'	=>$username,
							];
							$arrayPulangUpdate=[
								'afsAkId'					=>$cekStatusJamIn,
								'afsOut'					=>$jamsekarang,	
								'afsJenisAbsen'		=>3
							];
							return $this->dbUpdate("absen_finger_siswa",$where,$arrayPulangUpdate,2);
						}
						
					}
					else{ //jika data absen tidak di temukan
						$uuid	=	$this->getUuId();
						$arrayPulangInsert=[
							'afsId'						=>$uuid,
							'afsSklId'				=>$sklId,
							'afsSsaUsername'	=>$username,
							'afsRblId'				=>$rblId,
							'afsAkId'					=>"T",
							'afsDatetime'			=>$tglSekarang,
							'afsIn'						=>null,
							'afsOut'					=>$jamsekarang,
							'afsHari'					=>$namahari,
							'afsJenis'				=>$jensiAbsen,
							'afsJenisAbsen'		=>2
						];
						return $this->dbInsert("absen_finger_siswa",$arrayPulangInsert,2);

					} //end cekAbsenDiDatabase

				} else {
					//jika error 
					return BudutResponse::ErrorAbsenDiTolak();
				}

			} //end if cek nama hari

			

		}
	}

	// menerima send post data dari arduino -----------------------------------------------------


		
	//mengisi data ke server budutwj ---------------------------------------------------------------
	public function sendToServer(){
		$url = env('URL_SERVER_KIRM_ABSEN');
		$data = DB::table('absen_finger_siswa')->where('afsDatetime',"2022-09-20")->where("afsSinkron",0)->get();
		// $response = Http::withToken('smkbudutabsensirfid')->post($url,['data' =>$data]);
		// $array = $response->json();
		
		// return response()->json($array,200);
		
		
		$chunks = $data->chunk(300);
		$table="absen_finger_siswa";
		$noBerhasil =0; $noGagal=0;
		if($data->count() > 0){
			
			foreach($chunks as $chunk){
				
				$response = Http::withToken(env('TOKEN_BEARER'))->post($url,['data' =>$chunk]);
				$cekError = $response->failed(); //cek jika status >= 400 (error)
				$array = json_decode($response); //ubah data json ke array

				if($cekError){
					$array = $array->message;
					$response = [
						'status'  => 'error',
						'data'    => '',
						'message'	=> $array,
					];
					return response()->json($response,401);
					exit();
				}
				else{
					#jika status tidak error
					$array = $array->data;
					foreach($array as $val){
						$where = [
							'afsId'		=>$val->afsId,
						];
						$dataArray = [
							"afsSinkron"	=>$val->afsSinkron,
						];
						try {
							#edit data yang sudah berhasil di sinkron ke server
							$this->dbUpdate($table,$where,$dataArray,1); 
							$noBerhasil++;
						} catch (\Exception $e) {
							$noGagal++;
						}
					}
				}
				
			} //end foreach
			
			$pesan = 'Berhasil : '.$noBerhasil.' Gagal : '.$noGagal;
			return BudutResponse::successSinkronServerBudutWj($pesan);
		}
		else{
			$pesan ="DATA KOSONG";
			return BudutResponse::successSinkronServerBudutWj($pesan);
		}

		

		
	}

//mengisi data ke server budutwj ---------------------------------------------------------------




}
