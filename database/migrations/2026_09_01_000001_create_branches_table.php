<?php

use App\Models\Room;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Chi nhánh vốn nằm cứng trong code — giữ nguyên màu và nhãn cũ khi chuyển vào bảng. */
    private const SEED = [
        ['name' => 'Hotel',     'label' => 'Khách sạn', 'color' => '#1a3a6b'],
        ['name' => 'Villa',     'label' => 'Villa',     'color' => '#7c3aed'],
        ['name' => 'Residence', 'label' => 'Căn hộ',    'color' => '#0f766e'],
    ];

    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('label')->nullable();
            $table->string('color', 20)->default('#1a3a6b');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now  = now();
        $rows = [];

        foreach (self::SEED as $i => $branch) {
            $rows[] = $branch + ['sort_order' => $i, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
        }

        // Chi nhánh đã có trong bảng phòng nhưng không nằm trong danh sách cứng.
        $extra = Room::query()->distinct()->pluck('branch')
            ->filter()
            ->reject(fn ($name) => in_array($name, array_column(self::SEED, 'name'), true))
            ->values();

        foreach ($extra as $i => $name) {
            $rows[] = [
                'name'       => $name,
                'label'      => $name,
                'color'      => '#1a3a6b',
                'sort_order' => count(self::SEED) + $i,
                'is_active'  => true,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('branches')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
