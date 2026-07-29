<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products ADD COLUMN search_tsv tsvector');

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.products_search_tsv_update()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                NEW.search_tsv :=
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.name, '')), 'A') ||
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.short_description, '')), 'B') ||
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.description, '')), 'C') ||
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.sku, '')), 'A') ||
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.internal_code, '')), 'A');

                RETURN NEW;
            END
            $$;
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER products_search_tsv_trigger
            BEFORE INSERT OR UPDATE OF name, short_description, description, sku, internal_code
            ON products
            FOR EACH ROW
            EXECUTE FUNCTION public.products_search_tsv_update();
        SQL);

        DB::statement('UPDATE products SET search_tsv = NULL');
        DB::statement('CREATE INDEX products_search_tsv_idx ON products USING GIN (search_tsv)');
        DB::statement('CREATE INDEX products_name_trgm_idx ON products USING GIN (immutable_unaccent(name) gin_trgm_ops)');
        DB::statement('CREATE INDEX products_sku_trgm_idx ON products USING GIN (immutable_unaccent(coalesce(sku, \'\')) gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS products_sku_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_name_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS products_search_tsv_idx');
        DB::statement('DROP TRIGGER IF EXISTS products_search_tsv_trigger ON products');
        DB::statement('DROP FUNCTION IF EXISTS public.products_search_tsv_update()');

        if (Schema::hasColumn('products', 'search_tsv')) {
            DB::statement('ALTER TABLE products DROP COLUMN search_tsv');
        }
    }
};
