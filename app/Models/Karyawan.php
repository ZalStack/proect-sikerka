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
        'kode_pegawai',
        'email',
        'kata_sandi',
        'nama_lengkap',
        'posisi',
        'jabatan',
        'divisi',
        'golongan_darah',
        'no_kk',
        'sedang_melanjutkan_pendidikan',
        'jumlah_anak',
        'foto_profil',
        'nomor_telepon',
        'no_wa',
        'alamat',
        'tanggal_bergabung',
        'end_date',
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
        'perguruan_tinggi',
        'jurusan',
        'tahun_lulus',
        'nama_ibu_kandung',
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
        return $this->posisi === 'hr';
    }

    public function isKaryawan()
    {
        return $this->posisi === 'karyawan';
    }

    protected $casts = [
        'tanggal_bergabung' => 'date',
        'tanggal_lahir' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Accessor untuk status dengan label
    public function getStatusLabelAttribute()
    {
        $labels = [
            'Karyawan Tetap' => 'Karyawan Tetap',
            'Contract' => 'Kontrak',
            'Internship' => 'Magang'
        ];
        return $labels[$this->status] ?? $this->status;
    }

    // Accessor untuk status badge color
    public function getStatusBadgeAttribute()
    {
        $colors = [
            'Karyawan Tetap' => 'bg-[#2E7D3E] text-white',
            'Contract' => 'bg-[#FCC626] text-[#1B1B1B]',
            'Internship' => 'bg-[#00a2e9] text-white'
        ];
        return $colors[$this->status] ?? 'bg-gray-500 text-white';
    }
}
