<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;

/**
 * CRUD template pesan siap pakai (Fitur F23).
 *
 * Tiga jenis template: ban, violation, broadcast. Dipakai langsung di
 * halaman broadcast, form konfirmasi pelanggaran, dan form pesan alert
 * blokir presensi — mempercepat penulisan pesan berulang.
 */
class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $type = (string) $request->query('type', 'ban');
        if (!array_key_exists($type, MessageTemplate::TYPES)) {
            $type = 'ban';
        }

        $templates = MessageTemplate::where('type', $type)
            ->orderByDesc('is_default')
            ->orderByDesc('updated_at')
            ->paginate(10)
            ->withQueryString();

        $counts = MessageTemplate::selectRaw('type, count(*) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return view('superadmin.message-templates', compact('type', 'templates', 'counts'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($this->titleExists($data['type'], $data['title'])) {
            return back()
                ->withErrors(['title' => 'Template "' . $data['title'] . '" sudah ada untuk jenis ini.'])
                ->withInput();
        }

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            MessageTemplate::where('type', $data['type'])->update(['is_default' => false]);
        }

        MessageTemplate::create($data + [
            'is_default' => $isDefault,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        ActivityLog::record(
            'template.create',
            'Template pesan "' . $data['title'] . '" (' . MessageTemplate::TYPES[$data['type']] . ') ditambahkan.',
            ['module' => 'system', 'metadata' => ['type' => $data['type']]]
        );

        return redirect()
            ->route('superadmin.message-templates.index', ['type' => $data['type']])
            ->with('success', 'Template pesan berhasil ditambahkan.');
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $data = $this->validated($request);

        if ($this->titleExists($data['type'], $data['title'], $messageTemplate->id)) {
            return back()
                ->withErrors(['title' => 'Template "' . $data['title'] . '" sudah ada untuk jenis ini.'])
                ->withInput();
        }

        $isDefault = $request->boolean('is_default');
        if ($isDefault) {
            MessageTemplate::where('type', $data['type'])
                ->where('id', '!=', $messageTemplate->id)
                ->update(['is_default' => false]);
        }

        $messageTemplate->update($data + [
            'is_default' => $isDefault,
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record(
            'template.update',
            'Template pesan "' . $data['title'] . '" (' . MessageTemplate::TYPES[$data['type']] . ') diperbarui.',
            ['module' => 'system', 'metadata' => ['type' => $data['type'], 'id' => $messageTemplate->id]]
        );

        return redirect()
            ->route('superadmin.message-templates.index', ['type' => $data['type']])
            ->with('success', 'Template pesan berhasil diperbarui.');
    }

    public function destroy(MessageTemplate $messageTemplate)
    {
        $title = $messageTemplate->title;
        $type = $messageTemplate->type;
        $messageTemplate->delete();

        ActivityLog::record(
            'template.delete',
            'Template pesan "' . $title . '" (' . (MessageTemplate::TYPES[$type] ?? $type) . ') dihapus.',
            ['module' => 'system', 'metadata' => ['type' => $type]]
        );

        return redirect()
            ->route('superadmin.message-templates.index', ['type' => $type])
            ->with('success', 'Template pesan berhasil dihapus.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'type' => ['required', 'in:ban,violation,broadcast'],
            'title' => ['required', 'string', 'max:150'],
            'body' => ['required', 'string', 'max:5000'],
            'is_default' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'type.in' => 'Jenis template tidak dikenal.',
            'title.required' => 'Judul template wajib diisi.',
            'body.required' => 'Isi pesan template wajib diisi.',
        ]);
    }

    private function titleExists(string $type, string $title, ?int $ignoreId = null): bool
    {
        return MessageTemplate::where('type', $type)
            ->where('id', '!=', $ignoreId)
            ->whereRaw('lower(title) = ?', [mb_strtolower($title)])
            ->exists();
    }
}
