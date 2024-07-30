<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\MsSql;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccessLogController extends Controller
{


    public function index()
    {
        try {
            return response()->json([
                'status' => 'success',
                'data' => DB::table('ms_sql')->orderByDesc('primary_id')->first()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        \set_time_limit(0);

        try {
            DB::beginTransaction();

            $last_record = DB::table('ms_sql')
                ->where('ID', $request->emp_code)
                ->orderBy('datetime', 'desc')
                ->first();

            if ($last_record && $last_record->type == 'IN') {
                $type = 'OUT';
            } else {
                $type = 'IN';
            }

            $log = new MsSql;
            $log->local_primary_id = $request->id;
            $log->ID = $request->emp_code;
            $log->type = $type;
            $log->datetime = $request->punch_time;
            $log->device_name = $request->terminal_sn;
            $log->punching_time = $request->upload_time;
            $log->created_at = now();
            $log->updated_at = now();
            $log->save();

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Device Log Successfully updated !'], 200);
        } catch (\Throwable $th) {
            DB::rollback();
            info($th->getMessage());
            return response()->json(['status' => 'failed', 'message' => 'Device Log failed to update !'], 200);
        }
    }
}
