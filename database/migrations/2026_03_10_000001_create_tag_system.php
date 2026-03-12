<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * 1NF: Replace JSON `songs.tags` column with a proper relational tag system.
 *
 * Before: songs.tags JSON {"mood":["vui-ve"],"activity":["tap-gym"],"topic":["tinh-yeu"]}
 * After : tags(id, type, slug, label) + song_tags(song_id, tag_id) — each cell is atomic.
 */
return new class extends Migration
{
    // Canonical tag definitions (mirrors Song::$MOOD_TAGS, $ACTIVITY_TAGS, $TOPIC_TAGS)
    private array $tagDefinitions = [
        ['type' => 'mood', 'slug' => 'vui-ve',    'label' => 'Vui vẻ'],
        ['type' => 'mood', 'slug' => 'buon',       'label' => 'Buồn'],
        ['type' => 'mood', 'slug' => 'lang-man',   'label' => 'Lãng mạn'],
        ['type' => 'mood', 'slug' => 'energetic',  'label' => 'Energetic'],
        ['type' => 'mood', 'slug' => 'thu-gian',   'label' => 'Thư giãn'],
        ['type' => 'mood', 'slug' => 'hao-hung',   'label' => 'Hào hùng'],
        ['type' => 'mood', 'slug' => 'tuc-gian',   'label' => 'Tức giận'],
        ['type' => 'mood', 'slug' => 'tam-trang',  'label' => 'Tâm trạng'],

        ['type' => 'activity', 'slug' => 'tap-gym',   'label' => 'Tập gym'],
        ['type' => 'activity', 'slug' => 'chay-bo',   'label' => 'Chạy bộ'],
        ['type' => 'activity', 'slug' => 'lam-viec',  'label' => 'Làm việc'],
        ['type' => 'activity', 'slug' => 'lai-xe',    'label' => 'Lái xe'],
        ['type' => 'activity', 'slug' => 'yoga',      'label' => 'Yoga'],
        ['type' => 'activity', 'slug' => 'ngu',       'label' => 'Ngủ'],
        ['type' => 'activity', 'slug' => 'hoc-tap',   'label' => 'Học tập'],
        ['type' => 'activity', 'slug' => 'tiec-tung', 'label' => 'Tiệc tùng'],

        ['type' => 'topic', 'slug' => 'tinh-yeu',  'label' => 'Tình yêu'],
        ['type' => 'topic', 'slug' => 'chia-tay',  'label' => 'Chia tay'],
        ['type' => 'topic', 'slug' => 'gia-dinh',  'label' => 'Gia đình'],
        ['type' => 'topic', 'slug' => 'que-huong', 'label' => 'Quê hương'],
        ['type' => 'topic', 'slug' => 'cuoc-song', 'label' => 'Cuộc sống'],
        ['type' => 'topic', 'slug' => 'tuoi-tre',  'label' => 'Tuổi trẻ'],
        ['type' => 'topic', 'slug' => 'buon-vui',  'label' => 'Buồn vui'],
        ['type' => 'topic', 'slug' => 'ky-uc',     'label' => 'Ký ức'],
    ];

    public function up(): void
    {
        // ── 1. Create tags lookup table ──────────────────────────────────────
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['mood', 'activity', 'topic']);
            $table->string('slug', 50);
            $table->string('label', 100);
            $table->timestamps();

            // BCNF: (type, slug) is a candidate key — every FD comes from it
            $table->unique(['type', 'slug']);
        });

        // ── 2. Seed canonical tags ───────────────────────────────────────────
        $now = now();
        DB::table('tags')->insert(
            array_map(fn($t) => array_merge($t, [
                'created_at' => $now,
                'updated_at' => $now,
            ]), $this->tagDefinitions)
        );

        // ── 3. Create song_tags junction table ───────────────────────────────
        Schema::create('song_tags', function (Blueprint $table) {
            // Composite PK: no duplicate (song, tag) pairs
            $table->foreignId('song_id')
                  ->constrained('songs')
                  ->cascadeOnDelete();
            $table->foreignId('tag_id')
                  ->constrained('tags')
                  ->cascadeOnDelete();

            $table->primary(['song_id', 'tag_id']);
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('song_tags');
        Schema::dropIfExists('tags');
    }
};
