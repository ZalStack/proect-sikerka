<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(private NotificationService $notificationService)
    {
    }

    /**
     * Halaman daftar notifikasi lengkap: filter tipe + pagination 10 data/halaman
     * (next & previous), dipakai oleh KEDUA role (HR & karyawan).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $all = $this->notificationService->getAll($user);

        $selectedType = $request->get('type', 'semua');
        if ($selectedType !== 'semua') {
            $all = $all->where('type', $selectedType)->values();
        }

        $perPage = 10;
        $page = (int) $request->get('page', 1);
        $page = $page < 1 ? 1 : $page;

        $slice = $all->forPage($page, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $slice,
            $all->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $types = $this->notificationService->availableTypes($user);

        // Membuka halaman ini dianggap sudah melihat semua notifikasi terbaru.
        $this->notificationService->markSeen($user);

        return view('notifications.index', [
            'paginator' => $paginator,
            'types' => $types,
            'selectedType' => $selectedType,
        ]);
    }

    /**
     * Endpoint AJAX untuk dropdown notifikasi di navbar: 10 data terbaru + jumlah belum dibaca.
     * TIDAK menandai sebagai "sudah dibaca" -- itu dilakukan lewat endpoint markRead()
     * saat dropdown-nya benar-benar dibuka oleh user.
     */
    public function latest(Request $request)
    {
        $user = Auth::user();
        $all = $this->notificationService->getAll($user);
        $lastSeen = $this->notificationService->lastSeen($user);

        $unreadCount = $lastSeen
            ? $all->filter(fn ($item) => $item['created_at']->gt($lastSeen))->count()
            : $all->count();

        $latest = $all->take(10)->map(function ($item) use ($lastSeen) {
            return [
                'id' => $item['id'],
                'type' => $item['type'],
                'title' => $item['title'],
                'message' => $item['message'],
                'color' => $item['color'],
                'url' => $item['url'],
                'time_ago' => $item['created_at']->diffForHumans(),
                'is_new' => $lastSeen ? $item['created_at']->gt($lastSeen) : true,
            ];
        })->values();

        return response()->json([
            'items' => $latest,
            'unread_count' => $unreadCount,
            'total' => $all->count(),
        ]);
    }

    /**
     * Tandai semua notifikasi sudah dibaca. Dipanggil dari JS saat user membuka
     * dropdown notifikasi di navbar.
     */
    public function markRead(Request $request)
    {
        $user = Auth::user();
        $this->notificationService->markSeen($user);

        return response()->json(['success' => true]);
    }
}
