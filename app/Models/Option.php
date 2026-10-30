<?php

namespace App\Models;

use App\Filament\Enum\OptionGroup;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * One choice of a master-data list (program, periode, bank, ...) managed from the admin panel.
 * Registration data stores the choice's `value`; `label` is only what people see.
 *
 * @property OptionGroup $group
 * @property string $value
 * @property string $label
 * @property string|null $description
 * @property int $sort_order
 * @property bool $is_active
 */
class Option extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'value',
        'label',
        'description',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'group' => OptionGroup::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        $flush = function (Option $option): void {
            self::flush($option->group);

            $originalGroup = OptionGroup::tryFrom((string) $option->getRawOriginal('group'));

            if ($originalGroup !== null) {
                self::flush($originalGroup);
            }
        };

        static::saved($flush);
        static::deleted($flush);
    }

    public static function flush(OptionGroup $group): void
    {
        Cache::forget(self::cacheKey($group));
    }

    /**
     * `value => label` of a list in display order. Inactive choices are left out unless asked for,
     * because filters still have to find records saved with a choice that was retired later.
     *
     * @return array<string, string>
     */
    public static function list(OptionGroup $group, bool $onlyActive = true): array
    {
        return collect(self::rows($group))
            ->when($onlyActive, fn ($rows) => $rows->where('is_active', true))
            ->pluck('label', 'value')
            ->all();
    }

    /**
     * Choices for a form field: the active ones plus whatever the record already has,
     * so editing a record never blanks out a value that was retired in the meantime.
     *
     * @param  string|array<int, string>|null  $current
     * @return array<string, string>
     */
    public static function choices(OptionGroup $group, string|array|null $current = null): array
    {
        $options = self::list($group);
        $all = self::list($group, onlyActive: false);

        foreach ((array) $current as $value) {
            if (filled($value) && ! isset($options[$value])) {
                $options[$value] = ($all[$value] ?? (string) $value).' (nonaktif)';
            }
        }

        return $options;
    }

    /**
     * Label shown for a stored value; case is ignored because older records mix `Negeri` and `NEGERI`.
     */
    public static function label(OptionGroup $group, ?string $value): ?string
    {
        return self::find($group, $value)['label'] ?? null;
    }

    /**
     * Page header texts of a program; falls back to `$default` and then to its uppercased code.
     *
     * @return array{nameRegister: string, DescriptionRegister: string}
     */
    public static function programMeta(?string $type, string $default = 'apps'): array
    {
        $program = self::find(OptionGroup::Program, $type) ?? self::find(OptionGroup::Program, $default);

        if ($program === null) {
            return [
                'nameRegister' => strtoupper($default),
                'DescriptionRegister' => strtoupper($default),
            ];
        }

        return [
            'nameRegister' => $program['label'],
            'DescriptionRegister' => $program['description'] ?? $program['label'],
        ];
    }

    /**
     * How many registration records currently store this choice.
     */
    public function usageCount(): int
    {
        $query = RegistrationData::query();

        if ($this->group === OptionGroup::Tax) {
            return $query->whereJsonContains('tax_rates', $this->value)->count();
        }

        return $query->where($this->group->column(), $this->value)->count();
    }

    /**
     * @return array{value: string, label: string, description: string|null, is_active: bool}|null
     */
    private static function find(OptionGroup $group, ?string $value): ?array
    {
        if (blank($value)) {
            return null;
        }

        $needle = strtolower(trim($value));

        return collect(self::rows($group))
            ->first(fn (array $row): bool => strtolower($row['value']) === $needle);
    }

    /**
     * @return list<array{value: string, label: string, description: string|null, is_active: bool}>
     */
    private static function rows(OptionGroup $group): array
    {
        return Cache::rememberForever(
            self::cacheKey($group),
            fn (): array => self::query()
                ->where('group', $group->value)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get(['value', 'label', 'description', 'is_active'])
                ->map(fn (Option $option): array => [
                    'value' => $option->value,
                    'label' => $option->label,
                    'description' => $option->description,
                    'is_active' => $option->is_active,
                ])
                ->all(),
        );
    }

    private static function cacheKey(OptionGroup $group): string
    {
        return 'options.'.$group->value;
    }
}
