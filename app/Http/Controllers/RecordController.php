<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Record;
use App\Models\Instrument;

class RecordController extends Controller
{
    /**
     * Lazy load records via AJAX.
     * Expected query params:
     * - instrument_id (required)
     * - date_from (optional, Y-m-d)
     * - date_to (optional, Y-m-d)
     * - page (handled automatically by paginator)
     */
        public function lazy(Request $request)
        {
            $instrumentId = $request->query('instrument_id');
            if (!$instrumentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'instrument_id je obavezan.'
                ], 422);
            }

            $instrument = Instrument::find($instrumentId);
            if (!$instrument) {
                return response()->json([
                    'success' => false,
                    'message' => 'Instrument nije pronađen.'
                ], 404);
            }

            $dateFrom = $request->query('date_from');
            $dateTo = $request->query('date_to');
            $timeFrom = $request->query('time_from');
            $timeTo = $request->query('time_to');

            $query = $instrument->records();

            if ($dateFrom && $dateTo) {
                $query->whereBetween('datum', [$dateFrom, $dateTo]);
            } elseif ($dateFrom) {
                $query->where('datum', '>=', $dateFrom);
            } elseif ($dateTo) {
                $query->where('datum', '<=', $dateTo);
            }

            if ($timeFrom) {
                $query->where('vrijeme', '>=', $timeFrom);
            }
            if ($timeTo) {
                $query->where('vrijeme', '<=', $timeTo);
            }

            $query->orderByDesc('datum')->orderByDesc('vrijeme');

        // Default per page usklađen sa glavnim prikazom (10) da se izbjegne "rupa" kod kombinovanog učitavanja
        $perPage = (int) $request->query('per_page', 10);
            if ($perPage < 5) { $perPage = 5; }
            if ($perPage > 200) { $perPage = 200; }

            $paginator = $query->paginate($perPage);

            $recordsData = $paginator->map(function(Record $record) {
                return [
                    'id' => $record->id,
                    'datum' => $record->datum?->format('d.m.Y'),
                    'vrijeme' => $record->vrijeme?->format('H:i:s'),
                    'vrijednost' => number_format($record->vrijednost, 2)
                ];
            });

        return response()->json([
            'success' => true,
            'records' => $recordsData,
            'hasMore' => $paginator->hasMorePages(),
            'currentPage' => $paginator->currentPage(),
            'nextPage' => $paginator->currentPage() + 1,
            'total' => $paginator->total(),
            'perPage' => $paginator->perPage()
        ]);
    }
}
