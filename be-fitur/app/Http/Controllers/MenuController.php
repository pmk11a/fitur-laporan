<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\ReportService;

class MenuController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Get menu items based on user access level
     */
    public function index(Request $request): JsonResponse
    {
        $userAccess = $request->input('access', 0);
        $userLevel = $request->input('level', 0);

        try {
            $menus = $this->reportService->getMenuForUser($userAccess);

            if (empty($menus)) {
                return $this->getMockMenus($userAccess, $userLevel);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'menus' => $menus,
                    'permissions' => []
                ]
            ]);
        } catch (\Exception $e) {
            return $this->getMockMenus($userAccess, $userLevel);
        }
    }

    /**
     * Get sidebar menu - uses ReportService.getSidebarMenu() for correct hierarchy
     */
    public function sidebar(Request $request): JsonResponse
    {
        $userId = $request->input('userId', '');

        try {
            // Use ReportService which has the correct hierarchical logic
            $tree = $this->reportService->getSidebarMenu($userId);

            return response()->json([
                'success' => true,
                'data' => [
                    'menus' => $tree,
                    'permissions' => []
                ]
            ]);
        } catch (\Exception $e) {
            return $this->getMockSidebarMenus(0, 0);
        }
    }

    /**
     * Get mock menus for development
     */
    private function getMockMenus(int $access, int $level): JsonResponse
    {
        $menus = [
            ['KODEMENU' => 'REP001', 'Keterangan' => 'Laporan Penjualan', 'L0' => 1, 'ACCESS' => 1, 'OL' => 1],
            ['KODEMENU' => 'REP002', 'Keterangan' => 'Laporan Pembelian', 'L0' => 1, 'ACCESS' => 1, 'OL' => 2],
        ];

        $filtered = array_filter($menus, fn($m) => ($m['ACCESS'] & $access) > 0 || $m['ACCESS'] === 0);

        return response()->json([
            'success' => true,
            'data' => ['menus' => array_values($filtered), 'permissions' => []]
        ]);
    }

    /**
     * Get mock sidebar menus with proper hierarchy
     */
    private function getMockSidebarMenus(int $access, int $level): JsonResponse
    {
        $menus = [
            ['KODEMENU' => '010', 'Keterangan' => 'Master Accounting', 'L0' => 1, 'ACCESS' => 0, 'OL' => 1,
                'children' => [
                    ['KODEMENU' => '0101', 'Keterangan' => 'Daftar Perkiraan', 'L0' => 2, 'ACCESS' => 101, 'OL' => 1,
                        'children' => [
                            ['KODEMENU' => '010101', 'Keterangan' => 'Daftar Aktiva Tetap', 'L0' => 2, 'ACCESS' => 10101, 'OL' => 1, 'children' => []]
                        ]
                    ],
                    ['KODEMENU' => '0102', 'Keterangan' => 'Daftar Neraca', 'L0' => 2, 'ACCESS' => 102, 'OL' => 2, 'children' => []],
                ]
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => ['menus' => $menus, 'permissions' => []]
        ]);
    }
}
