<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class PengumumanController extends Controller
{
    // WhatsApp Gateway Configuration
    private $whatsappNumber = '082123439604';

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
        // Debug: log request data
        \Log::info('Pengumuman store request:', $request->all());

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
            'created_by' => Auth::id(), // Pastikan user login
            'is_sent_to_whatsapp' => false,
            'whatsapp_status' => 'pending',
        ];

        // Upload gambar jika ada
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . Str::slug($request->judul) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('pengumuman', $filename, 'public');
            $data['gambar'] = $path;
        }

        // Debug: check data before save
        \Log::info('Data to save:', $data);

        try {
            $pengumuman = Pengumuman::create($data);

            // Kirim ke WhatsApp jika dicentang
            if ($request->has('send_whatsapp')) {
                $this->sendToWhatsApp($pengumuman);
            }

            return redirect()->route('hr.pengumuman.index')
                ->with('success', 'Pengumuman berhasil ditambahkan' .
                    ($request->has('send_whatsapp') ? ' dan dikirim ke WhatsApp' : ''));

        } catch (\Exception $e) {
            \Log::error('Error saving pengumuman:', ['error' => $e->getMessage()]);
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
    public function sendToWhatsApp($pengumuman)
    {
        try {
            // Update status
            $pengumuman->whatsapp_status = 'sent';
            $pengumuman->is_sent_to_whatsapp = true;
            $pengumuman->sent_at = Carbon::now();
            $pengumuman->save();

            // Format pesan
            $message = $this->formatWhatsAppMessage($pengumuman);

            // Buat link WhatsApp
            $whatsappLink = "https://wa.me/{$this->whatsappNumber}?text=" . urlencode($message);

            // Simpan link atau kirim ke log
            \Log::info('WhatsApp Link:', ['link' => $whatsappLink]);

            return true;

        } catch (\Exception $e) {
            \Log::error('Error sending to WhatsApp:', ['error' => $e->getMessage()]);
            $pengumuman->whatsapp_status = 'failed';
            $pengumuman->save();
            return false;
        }
    }

    // Kirim ulang ke WhatsApp
    public function resendWhatsApp($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $result = $this->sendToWhatsApp($pengumuman);

        if ($result) {
            return redirect()->route('hr.pengumuman.index')
                ->with('success', 'Pengumuman berhasil dikirim ulang ke WhatsApp');
        } else {
            return redirect()->route('hr.pengumuman.index')
                ->with('error', 'Gagal mengirim ulang pengumuman ke WhatsApp');
        }
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

    // Manual send via WhatsApp button
    public function manualSendWhatsApp($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $message = $this->formatWhatsAppMessage($pengumuman);

        $whatsappLink = "https://wa.me/{$this->whatsappNumber}?text=" . urlencode($message);

        return response()->json([
            'success' => true,
            'message' => $message,
            'link' => $whatsappLink,
            'phone' => $this->whatsappNumber
        ]);
    }
}
