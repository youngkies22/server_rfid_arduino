<?php
namespace App\SendRespon;

//use Illuminate\Http\JsonResponse;

use Illuminate\Http\Response;
use Ramsey\Uuid\Type\Integer;

/**
 * Dev       : @mryes
 * Aplikasi  : CBT BUDUT <SMK Budi Utomo Way Jepara>
 * Turunan   : Candy CBT <https://www.cbtcandy.com/>
 * Location  : Way Jepara Lampung Timur
 * @author mryes way jepara <mryes2210@gmail.com>
 */
class BudutResponse
{


    /**
     * Status 403 Forbidden
     *
     * @param string $message
     * @return Response
     */
    public static function forbidden(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'you do not have access to this source'
        ],403, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 404 Not Found
     *
     * @param string $message
     * @return Response
     */
    public static function notFound(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'data not found'
        ],404, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 400 Bad Request
     *
     * @param string $message
     * @return Response
     */
    public static function badRequest(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'bad request'
        ],400, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept
     *
     * @param string $message
     * @return Response
     */
    public static function accept(string $message = '')
    {
        return new Response([
            'error' => false,
            'message' => $message != '' ? $message : 'success'
        ],200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept data
     *
     * @param mixed $data
     * @return Response
     */
    public static function acceptData($data)
    {
        return new Response([
            'error' => false,
            'data' => $data
        ], 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept data custom
     *
     * @param mixed $data
     * @return  Response
     */
    public static function acceptCustom($data)
    {
        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 500 Internal server error
     *
     * @param string $message
     * @return Response
     */
    public static function internalServerError(string $message = '')
    {
        return new Response([
            'error' => true,
            'message' => $message != '' ? $message : 'internal server error'
        ], 500, [
            'Content-Type' => 'application/json'
        ]);
    }

// repost budutwj -------------------------------------------------------------------------------------------------
    /**
     * Status 403 Forbidden
     * Error kartu tidak di temukan
     * @param string $message
     * @return Response
     */
    public static function ErrorNotKartu()
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'error',
            'pesan'		=> 'ERROR KARTU',
            'pesan2'	=> 'TIDAK DITEMUKAN',
            'kode'		=>  403
        ], 403, [
            'Content-Type' => 'application/json'
        ]);
    }
    /**
     * Status 403 Forbidden
     * Hari Libur
     * @param string $message
     * @return Response
     */
    public static function ErrorHariLibur()
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'error',
            'pesan'		=> 'Hari Ini Libur',
            'pesan2'	=> 'Anda Lupa ?',
            'kode'		=>  403
        ], 403, [
            'Content-Type' => 'application/json'
        ]);
    }
    /**
     * Status 403 Forbidden
     * Absen di Total
     * Karna Batas Waktu Absensi Habis
     * @param string $message
     * @return Response
     */
    public static function ErrorAbsenDiTolak()
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'error',
            'pesan'		=> 'ABSEN ANDA',
            'pesan2'	=> 'DI TOLAK',
            'kode'		=>  403
        ], 403, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept
     * anda sudab absensi
     * @param string $message
     * @return Response
     */
    public static function successAndaSudahAbsen()
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'oke',
			'pesan'		=> 'ANDA SUDAH',
			'pesan2'    => 'ABSEN',
			'kode'		=> 200
        ],200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept
     * absensi siswa berhasil
     * @param int $status
     * @return Response
     */
    public static function successAbsensBerhasil(int $status=0 )
    {
        
        if($status == 1){
            $pesan ="ABSENSI MASUK";
            $pesan2 = "TELAH BERHASIL";
        }
        elseif($status ==2){
            $pesan ="ABSENSI PULANG";
            $pesan2 = "TELAH BERHASIL";
        }
        else{
            $pesan ="ABSENSI";
            $pesan2 = "ERROR";
        }
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'oke',
            'pesan'		=> $pesan != '' ? $pesan : '',
            'pesan2'    => $pesan2 != '' ? $pesan2 : '',
            'kode'		=> 200
        ],200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * Status 200 Accept
     * berhasil sinkron ke server budutwj
     * @param string $message
     * @return Response
     */
    public static function successSinkronServerBudutWj($pesan)
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'  	=> 'success',
            'data'		=> '',
            'message'   => $pesan,
        ],200, [
            'Content-Type' => 'application/json'
        ]);
    }


    /**
     * Status 500 Internal server error
     * GAGAL EDIT ABSENSI
     * @param string $message
     * @return Response
     */
    public static function ErrorAbsensiEdit()
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'oke',
            'pesan'		=> 'GAGAL EDIT',
            'pesan2'    => 'ABSENSI',
            'kode'		=> 500,
        ],500, [
            'Content-Type' => 'application/json'
        ]);
    }
    /**
     * Status 500 Internal server error
     * GAGAL TAMBAH ABSENSI
     * @param string $message
     * @return Response
     */
    public static function ErrorAbsensiTambah()
    {
        return new Response([
            'tgl'       =>  date('Y-m-d H:i'),
            'hari'      =>  date('l', strtotime(date('Y-m-d'))),
            'status'	=> 'oke',
            'pesan'		=> 'GAGAL TAMBAH',
            'pesan2'    => 'ABSENSI',
            'kode'		=> 500
        ],500, [
            'Content-Type' => 'application/json'
        ]);
    }


    



}
