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
        echo "🔄 Transfert MySQL → PostgreSQL...\n\n";

        // 1. Définir dynamiquement la connexion MySQL locale
        Config::set('database.connections.mysql_temp', [
            'driver' => 'mysql',
            'host' => env('TRANSFER_MYSQL_HOST', '127.0.0.1'),
            'port' => env('TRANSFER_MYSQL_PORT', '3306'),
            'database' => env('TRANSFER_MYSQL_DATABASE', 'ecommerce_db'),
            'username' => env('TRANSFER_MYSQL_USERNAME', 'root'),
            'password' => env('TRANSFER_MYSQL_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);

        // 2. Définir dynamiquement la connexion PostgreSQL distante (via variables d'environnement)
        Config::set('database.connections.pgsql_temp', [
            'driver' => 'pgsql',
            'host' => env('TRANSFER_PGSQL_HOST', ''),
            'port' => env('TRANSFER_PGSQL_PORT', '5432'),
            'database' => env('TRANSFER_PGSQL_DATABASE', ''),
            'username' => env('TRANSFER_PGSQL_USERNAME', ''),
            'password' => env('TRANSFER_PGSQL_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'require',
        ]);

        if (empty(env('TRANSFER_PGSQL_HOST'))) {
            echo "⚠️  Variables TRANSFER_PGSQL_* non configurées. Veuillez les définir dans .env pour exécuter le transfert.\n";
            return;
        }

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
