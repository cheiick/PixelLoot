<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DataTransferSeeder extends Seeder
{
    public function run()
    {
        echo "🔄 Début du transfert MySQL → PostgreSQL...\n\n";

        // Fonction helper pour transférer une table
        $this->transferTable('categories');
        $this->transferTable('products');
        $this->transferTable('users');
        $this->transferTable('orders');
        $this->transferTable('order_items');
        $this->transferTable('coupons');
        $this->transferTable('site_settings');
        $this->transferTable('contact_messages');

        echo "\n🎉 Transfert terminé !\n";
    }

    private function transferTable($table)
    {
        try {
            // Récupérer les colonnes de la source (MySQL)
            $sourceColumns = Schema::connection('mysql_local')->getColumnListing($table);
            
            // Récupérer les colonnes de la destination (PostgreSQL)
            $destColumns = Schema::getColumnListing($table);
            
            // Colonnes communes
            $commonColumns = array_intersect($sourceColumns, $destColumns);
            
            // Exclure 'id' du transfert (on garde les IDs de MySQL)
            $columnsToTransfer = array_diff($commonColumns, []);
            
            // Récupérer les données de MySQL
            $data = DB::connection('mysql_local')->table($table)->get();
            
            if ($data->isEmpty()) {
                echo "⚠️  $table : aucune donnée à transférer\n";
                return;
            }
            
            $count = 0;
            foreach ($data as $row) {
                $rowArray = (array) $row;
                // Ne garder que les colonnes communes
                $filteredData = array_intersect_key($rowArray, array_flip($columnsToTransfer));
                
                DB::table($table)->updateOrInsert(
                    ['id' => $row->id],
                    $filteredData
                );
                $count++;
            }
            
            echo "✅ $table : $count enregistrements transférés\n";
            
        } catch (\Exception $e) {
            echo "❌ $table : " . $e->getMessage() . "\n";
        }
    }
}