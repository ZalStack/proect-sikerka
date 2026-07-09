<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PengumumanController extends Controller
{
    // WhatsApp Configuration - Updated
    private $whatsappNumber = '628111912340'; // +62 811-1912-340 (tanpa + dan tanpa tanda hubung)

    public function index()
    {
        $pengumuman = Pengumuman::with('creator')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('hr.pengumuman.index', compact('pengumuman'));
    }

    public function create()
    {
        return view('hr.pengumuman.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target' => 'required|in:semua,hr,karyawan',
            'send_whatsapp' => 'nullable',
        ]);

        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'target' => $request->target,
            'created_by' => Auth::id(),
            'is_sent_to_whatsapp' => false,
            'whatsapp_status' => 'pending',
        ];

        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        try {
            $pengumuman = Pengumuman::create($data);

            if ($request->has('send_whatsapp')) {
                $message = $this->formatWhatsAppMessage($pengumuman);
                $whatsappUrl = "https://wa.me/{$this->whatsappNumber}?text=" . urlencode($message);

                $pengumuman->is_sent_to_whatsapp = true;
                $pengumuman->sent_at = Carbon::now();
                $pengumuman->whatsapp_status = 'sent';
                $pengumuman->save();

                return redirect()->away($whatsappUrl);
            }

            return redirect()->route('hr.pengumuman.index')
                ->with('success', 'Pengumuman berhasil ditambahkan');

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan pengumuman: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $pengumuman = Pengumuman::with('creator')->findOrFail($id);
        return view('hr.pengumuman.show', compact('pengumuman'));
    }

    public function edit($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        return view('hr.pengumuman.edit', compact('pengumuman'));
    }

    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:200',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'target' => 'required|in:semua,hr,karyawan',
        ]);

        $data = [
            'judul' => $request->judul,
            'isi' => $request->isi,
            'target' => $request->target,
        ];

        if ($request->hasFile('gambar')) {
            if ($pengumuman->gambar) {
                Storage::disk('public')->delete($pengumuman->gambar);
            }
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        $pengumuman->update($data);

        return redirect()->route('hr.pengumuman.index')
            ->with('success', 'Pengumuman berhasil diupdate');
    }

    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        if ($pengumuman->gambar) {
            Storage::disk('public')->delete($pengumuman->gambar);
        }
        $pengumuman->delete();

        return redirect()->route('hr.pengumuman.index')
            ->with('success', 'Pengumuman berhasil dihapus');
    }

    // Kirim ke WhatsApp
    public function sendWhatsApp($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $message = $this->formatWhatsAppMessage($pengumuman);
        $whatsappUrl = "https://wa.me/{$this->whatsappNumber}?text=" . urlencode($message);

        $pengumuman->is_sent_to_whatsapp = true;
        $pengumuman->sent_at = Carbon::now();
        $pengumuman->whatsapp_status = 'sent';
        $pengumuman->save();

        return redirect()->away($whatsappUrl);
    }

    // Kirim ke WhatsApp dengan nomor tertentu
    public function sendWhatsAppToNumber($id, $phone = null)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $message = $this->formatWhatsAppMessage($pengumuman);

        if ($phone) {
            $cleanPhone = $this->cleanPhoneNumber($phone);
            $whatsappUrl = "https://wa.me/{$cleanPhone}?text=" . urlencode($message);
        } else {
            $whatsappUrl = "https://wa.me/{$this->whatsappNumber}?text=" . urlencode($message);
        }

        $pengumuman->is_sent_to_whatsapp = true;
        $pengumuman->sent_at = Carbon::now();
        $pengumuman->whatsapp_status = 'sent';
        $pengumuman->save();

        return redirect()->away($whatsappUrl);
    }

    // Format pesan WhatsApp
    private function formatWhatsAppMessage($pengumuman)
    {
        $message = "📢 *PENGUMUMAN*\n\n";
        $message .= "*{$pengumuman->judul}*\n\n";
        $message .= "{$pengumuman->isi}\n\n";
        $message .= "📅 *Tanggal:* " . Carbon::parse($pengumuman->created_at)->format('d-m-Y H:i') . "\n";
        $message .= "👤 *Dibuat oleh:* " . ($pengumuman->creator ? $pengumuman->creator->nama_lengkap : 'HR') . "\n\n";
        $message .= "---\n";
        $message .= "📌 *Target:* " . $pengumuman->target_label . "\n";
        $message .= "🆔 *ID:* #" . str_pad($pengumuman->id, 4, '0', STR_PAD_LEFT);

        return $message;
    }

    // Clean phone number
    private function cleanPhoneNumber($phone)
    {
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        if (strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }

        $phone = str_replace('+', '', $phone);

        return $phone;
    }

    // Dapatkan daftar nomor WhatsApp karyawan
    public function getWhatsAppContacts()
    {
        $karyawans = Karyawan::whereNotNull('no_wa')
            ->orWhereNotNull('nomor_telepon')
            ->select('id', 'nama_lengkap', 'no_wa', 'nomor_telepon')
            ->get();

        $contacts = [];
        foreach ($karyawans as $k) {
            $phone = $k->no_wa ?? $k->nomor_telepon;
            if ($phone) {
                $contacts[] = [
                    'id' => $k->id,
                    'nama' => $k->nama_lengkap,
                    'phone' => $this->cleanPhoneNumber($phone)
                ];
            }
        }

        return $contacts;
    }

    // Tampilkan halaman pilih kontak WhatsApp
    public function selectContact($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $contacts = $this->getWhatsAppContacts();

        return view('hr.pengumuman.select-contact', compact('pengumuman', 'contacts'));
    }

    // Resend WhatsApp
    public function resendWhatsApp($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $message = $this->formatWhatsAppMessage($pengumuman);
        $whatsappUrl = "https://wa.me/{$this->whatsappNumber}?text=" . urlencode($message);

        $pengumuman->is_sent_to_whatsapp = true;
        $pengumuman->sent_at = Carbon::now();
        $pengumuman->whatsapp_status = 'sent';
        $pengumuman->save();

        return redirect()->away($whatsappUrl);
    }
}
