<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');
        DB::statement('CREATE EXTENSION IF NOT EXISTS unaccent');

        DB::statement(<<<'SQL'
            DO $$
            BEGIN
                IF NOT EXISTS (
                    SELECT 1
                    FROM pg_ts_config
                    WHERE cfgname = 'pt_unaccent'
                ) THEN
                    CREATE TEXT SEARCH CONFIGURATION public.pt_unaccent (COPY = pg_catalog.portuguese);
                END IF;
            END
            $$;
        SQL);

        DB::statement(<<<'SQL'
            ALTER TEXT SEARCH CONFIGURATION public.pt_unaccent
            ALTER MAPPING FOR hword, hword_part, word
            WITH unaccent, portuguese_stem;
        SQL);

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.immutable_unaccent(text)
            RETURNS text
            LANGUAGE sql
            IMMUTABLE
            PARALLEL SAFE
            STRICT
            AS $$
                SELECT public.unaccent('public.unaccent'::regdictionary, $1)
            $$;
        SQL);

        DB::statement('ALTER TABLE posts ADD COLUMN search_tsv tsvector');

        DB::statement(<<<'SQL'
            CREATE OR REPLACE FUNCTION public.posts_search_tsv_update()
            RETURNS trigger
            LANGUAGE plpgsql
            AS $$
            BEGIN
                NEW.search_tsv :=
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.title, '')), 'A') ||
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.excerpt, '')), 'B') ||
                    setweight(to_tsvector('public.pt_unaccent', coalesce(NEW.content, '')), 'C');

                RETURN NEW;
            END
            $$;
        SQL);

        DB::statement(<<<'SQL'
            CREATE TRIGGER posts_search_tsv_trigger
            BEFORE INSERT OR UPDATE OF title, excerpt, content
            ON posts
            FOR EACH ROW
            EXECUTE FUNCTION public.posts_search_tsv_update();
        SQL);

        DB::statement('UPDATE posts SET search_tsv = NULL');

        DB::statement('CREATE INDEX posts_search_tsv_idx ON posts USING GIN (search_tsv)');
        DB::statement('CREATE INDEX posts_title_trgm_idx ON posts USING GIN (immutable_unaccent(title) gin_trgm_ops)');
        DB::statement('CREATE INDEX posts_slug_trgm_idx ON posts USING GIN (immutable_unaccent(slug) gin_trgm_ops)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS posts_slug_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS posts_title_trgm_idx');
        DB::statement('DROP INDEX IF EXISTS posts_search_tsv_idx');
        DB::statement('DROP TRIGGER IF EXISTS posts_search_tsv_trigger ON posts');
        DB::statement('DROP FUNCTION IF EXISTS public.posts_search_tsv_update()');
        DB::statement('DROP FUNCTION IF EXISTS public.immutable_unaccent(text)');

        if (Schema::hasColumn('posts', 'search_tsv')) {
            DB::statement('ALTER TABLE posts DROP COLUMN search_tsv');
        }
    }
};
