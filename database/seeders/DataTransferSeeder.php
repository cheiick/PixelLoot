<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Config;

class DataTransferSeeder extends Seeder
{
    public function run()
    {
        echo "🔄 Transfert MySQL → PostgreSQL (Render)...\n\n";

        // 1. Définir dynamiquement la connexion MySQL locale (en dur)
        Config::set('database.connections.mysql_temp', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'ecommerce_db',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        // 2. Définir dynamiquement la connexion PostgreSQL Render (en dur)
        Config::set('database.connections.pgsql_temp', [
            'driver' => 'pgsql',
            'host' => 'dpg-dbieg1ijnfac73bf49ag-a.frankfurt-postgres.render.com',
            'port' => '5432',
            'database' => 'monshop_db_lzgw',
            'username' => 'monshop_db_lzgw_user',
            'password' => 'DD4TRCLRQiAloaKjnbh6lIpHROdXa3PC', // ⚠️ REMPLACE CECI PAR TON VRAI MDP
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'require', // Obligatoire pour Render
        ]);

        $tables = [
            'categories', 'products', 'users', 'orders', 
            'order_items', 'coupons', 'site_settings', 'contact_messages'
        ];

        foreach ($tables as $table) {
            $this->transferTable($table);
        }

        echo "\n🎉 Transfert terminé !\n";
    }

    private function transferTable($table)
    {
        try {
            // On utilise les NOMS des connexions temporaires (chaînes de caractères)
            $source = 'mysql_temp';
            $dest = 'pgsql_temp';

            // Lire les colonnes de MySQL
            $sourceColumns = Schema::connection($source)->getColumnListing($table);
            
            // Lire les colonnes de PostgreSQL
            $destColumns = Schema::connection($dest)->getColumnListing($table);
            
            // Garder uniquement les colonnes communes
            $commonColumns = array_intersect($sourceColumns, $destColumns);
            
            // Récupérer les données de MySQL
            $data = DB::connection($source)->table($table)->get();
            
            if ($data->isEmpty()) {
                echo "⚠️  $table : aucune donnée\n";
                return;
            }
            
            $count = 0;
            foreach ($data as $row) {
                $rowArray = (array) $row;
                // Filtrer pour ne garder que les colonnes communes
                $filteredData = array_intersect_key($rowArray, array_flip($commonColumns));
                
                // Insérer ou mettre à jour dans PostgreSQL
                DB::connection($dest)->table($table)->updateOrInsert(
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