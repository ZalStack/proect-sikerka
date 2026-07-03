<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Karyawan extends Authenticatable
{
    use Notifiable;

    protected $table = 'karyawans';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nip',
        'email',
        'kata_sandi',
        'nama_depan',
        'nama_belakang',
        'nama_lengkap',
        'role',
        'jabatan',
        'divisi',
        'golongan_darah',
        'no_kk',
        'gelar_pendidikan',
        'sedang_melanjutkan_pendidikan',
        'jumlah_anak',
        'foto_profil',
        'nomor_telepon',
        'alamat',
        'tanggal_bergabung',
        'end_date',
        'total_hari_kerja',
        'reason_resigned',
        'status',
        'nik',
        'npwp',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status_pernikahan',
        'pendidikan_terakhir',
        'pendidikan_terakhir_new',
        'universitas',
        'jurusan',
        'tahun_lulus',
        'nama_kontak_darurat',
        'telepon_kontak_darurat',
    ];

    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    public function getAuthPassword()
    {
        return $this->kata_sandi;
    }

    public function isHr()
    {
        return $this->role === 'hr';
    }

    public function isKaryawan()
    {
        return $this->role === 'karyawan';
    }

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'tanggal_lahir' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
