<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // If legacy deposits table exists, backfill to new receipts/items
        if (!DB::getSchemaBuilder()->hasTable('deposits')) {
            return;
        }

        $rows = DB::table('deposits')->orderBy('id')->get();
        foreach ($rows as $row) {
            // Create a receipt with the same metadata and total = amount
            $receiptId = DB::table('deposit_receipts')->insertGetId([
                'date' => $row->date,
                'member_id' => $row->member_id,
                'total_amount' => $row->amount,
                'payment_method' => $row->payment_method,
                'note' => $row->note,
                'added_by' => $row->added_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create a single item matching the old row type/amount
            DB::table('deposit_items')->insert([
                'receipt_id' => $receiptId,
                'type' => $row->type,
                'amount' => $row->amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Update any cashbook rows that referenced Deposit to now reference the new receipt
            DB::table('cashbooks')
                ->where('reference_type', 'App\\Models\\Deposit')
                ->where('reference_id', $row->id)
                ->update([
                    'reference_type' => 'App\\Models\\DepositReceipt',
                    'reference_id' => $receiptId,
                ]);
        }
    }

    public function down(): void
    {
        // No-op; do not rollback data transformation
    }
};
