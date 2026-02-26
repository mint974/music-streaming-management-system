<?php

namespace Database\Seeders;

use App\Models\Genre;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GenreSeeder extends Seeder
{
    /**
     * Trả về relative path ảnh bìa trong storage/app/public/
     * nếu file tồn tại, ngược lại trả null.
     */
    private function coverPath(string $filename): ?string
    {
        $path = storage_path('app/public/genre/' . $filename);
        return file_exists($path) ? 'genre/' . $filename : null;
    }

    public function run(): void
    {
        $genres = [
            [
                'name'        => 'Pop',
                'cover_image' => $this->coverPath('pop.jpg'),
                'description' => 'Nhạc pop – thể loại nhạc đại chúng với giai điệu bắt tai, dễ nghe, phổ biến toàn cầu.',
                'icon'        => 'fa-solid fa-star',
                'color'       => '#ec4899',
                'sort_order'  => 1,
                'is_active'   => true,
            ],
            [
                'name'        => 'Rock',
                'cover_image' => $this->coverPath('Rock.jpg'),
                'description' => 'Nhạc rock – thể loại nhạc mạnh mẽ, sôi động với guitar điện, trống và bass.',
                'icon'        => 'fa-solid fa-guitar',
                'color'       => '#ef4444',
                'sort_order'  => 2,
                'is_active'   => true,
            ],
            [
                'name'        => 'Ballad',
                'cover_image' => $this->coverPath('Ballad.jpg'),
                'description' => 'Nhạc ballad – những ca khúc chậm rãi, da diết, giàu cảm xúc và lời ca sâu lắng.',
                'icon'        => 'fa-solid fa-heart',
                'color'       => '#f43f5e',
                'sort_order'  => 3,
                'is_active'   => true,
            ],
            [
                'name'        => 'Rap / Hip-hop',
                'cover_image' => $this->coverPath('Rap.jpg'),
                'description' => 'Nhạc rap và hip-hop – kết hợp lời rap sắc bén, beat mạnh và phong cách đường phố.',
                'icon'        => 'fa-solid fa-microphone',
                'color'       => '#f59e0b',
                'sort_order'  => 4,
                'is_active'   => true,
            ],
            [
                'name'        => 'EDM',
                'cover_image' => $this->coverPath('EDM.jpg'),
                'description' => 'Electronic Dance Music – nhạc điện tử sôi động, được thiết kế cho sàn nhảy và các lễ hội âm nhạc.',
                'icon'        => 'fa-solid fa-bolt',
                'color'       => '#8b5cf6',
                'sort_order'  => 5,
                'is_active'   => true,
            ],
            [
                'name'        => 'R&B',
                'cover_image' => $this->coverPath('R&B.jpg'),
                'description' => 'Rhythm and Blues – thể loại nhạc mượt mà, passionate, kết hợp giữa soul, funk và hip-hop.',
                'icon'        => 'fa-solid fa-fire',
                'color'       => '#f97316',
                'sort_order'  => 6,
                'is_active'   => true,
            ],
            [
                'name'        => 'Jazz',
                'cover_image' => $this->coverPath('Jazz.jpg'),
                'description' => 'Nhạc jazz – thể loại âm nhạc tinh tế với phong cách ứng tấu tự do, saxophone và piano đặc trưng.',
                'icon'        => 'fa-solid fa-compact-disc',
                'color'       => '#0ea5e9',
                'sort_order'  => 7,
                'is_active'   => true,
            ],
            [
                'name'        => 'Classical',
                'cover_image' => $this->coverPath('Classical.jpg'),
                'description' => 'Nhạc cổ điển – tinh hoa âm nhạc từ thế kỷ 17–19 với dàn nhạc giao hưởng và các nhà soạn nhạc bậc thầy.',
                'icon'        => 'fa-solid fa-music',
                'color'       => '#6366f1',
                'sort_order'  => 8,
                'is_active'   => true,
            ],
            [
                'name'        => 'Country',
                'cover_image' => $this->coverPath('Country.jpg'),
                'description' => 'Nhạc đồng quê – thể loại nhạc Mỹ mang âm hưởng cây đàn guitar thùng, banjo và cuộc sống nông thôn.',
                'icon'        => 'fa-solid fa-hat-cowboy',
                'color'       => '#84cc16',
                'sort_order'  => 9,
                'is_active'   => true,
            ],
            [
                'name'        => 'K-Pop',
                'cover_image' => $this->coverPath('K-Pop.jpg'),
                'description' => 'Nhạc pop Hàn Quốc – kết hợp âm nhạc bắt tai, vũ đạo điêu luyện và phong cách trình diễn đẳng cấp.',
                'icon'        => 'fa-solid fa-crown',
                'color'       => '#a855f7',
                'sort_order'  => 10,
                'is_active'   => true,
            ],
            [
                'name'        => 'V-Pop',
                'cover_image' => $this->coverPath('V-Pop.jpg'),
                'description' => 'Nhạc pop Việt Nam – thể loại nhạc trẻ Việt với giai điệu đa dạng từ ballad, dance pop đến fusion.',
                'icon'        => 'fa-solid fa-flag',
                'color'       => '#10b981',
                'sort_order'  => 11,
                'is_active'   => true,
            ],
            [
                'name'        => 'Indie',
                'cover_image' => $this->coverPath('Indie.jpg'),
                'description' => 'Nhạc indie – âm nhạc độc lập, sáng tạo không theo khuôn mẫu, đề cao cá tính và tự do nghệ thuật.',
                'icon'        => 'fa-solid fa-leaf',
                'color'       => '#6ee7b7',
                'sort_order'  => 12,
                'is_active'   => true,
            ],
            [
                'name'        => 'Lo-fi',
                'cover_image' => $this->coverPath('Lo-fi.jpg'),
                'description' => 'Lo-fi hip hop – âm nhạc thư giãn, chill với tiếng vinyl ấm, thường dùng để học tập và thư giãn.',
                'icon'        => 'fa-solid fa-headphones',
                'color'       => '#94a3b8',
                'sort_order'  => 13,
                'is_active'   => true,
            ],
        ];

        foreach ($genres as $data) {
            Genre::updateOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, ['slug' => Str::slug($data['name'])])
            );
            $cover = $data['cover_image'] ?? null;
            $this->command->line('  ✓ ' . $data['name'] . ': ' . ($cover ?? '<không có ảnh>'));
        }

        $this->command->info('✅ Đã tạo / cập nhật ' . count($genres) . ' thể loại nhạc.');
    }
}
