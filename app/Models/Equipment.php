<?php

namespace App\Models;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Equipment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'serial_number',
        'model',
        'brand',
        'status',
        'imei',
        'traccar_id',
        'phone_number',
        'chip_provider',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Get the vehicle that uses this equipment.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Get the vehicle that uses this equipment.
     * This is used for relationship management in Filament.
     */
    public function vehicles(): HasOne
    {
        return $this->hasOne(Vehicle::class, 'equipment_id');
    }

    /**
     * Get the customers who have access to this equipment.
     * Multiple customers can access a single equipment for monitoring and management purposes.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class)->withTimestamps();
    }

    /**
     * Get the services associated with the equipment.
     */
    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the history entries for this equipment.
     */
    public function history(): HasMany
    {
        return $this->hasMany(EquipmentHistory::class);
    }

    /**
     * Boot method para registrar os eventos do modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Captura o evento de atualização
        static::updating(function ($equipment) {
            // Se o status foi alterado, registra no histórico
            if ($equipment->isDirty('status')) {
                EquipmentHistory::create([
                    'equipment_id' => $equipment->id,
                    'user_id' => Auth::id(),
                    'previous_status' => $equipment->getOriginal('status'),
                    'new_status' => $equipment->status,
                    'notes' => null, // Para notas manuais, precisaríamos implementar um campo no form
                ]);
            }
        });
    }

    /**
     * Retorna o nome amigável do status
     */
    public function getStatusNameAttribute(): string
    {
        $statusMap = [
            'in_stock' => 'Em Estoque',
            'with_technician' => 'Com Técnico',
            'with_customer' => 'Com Cliente',
            'defective' => 'Com Defeito',
            'maintenance' => 'Em Manutenção',
        ];

        return $statusMap[$this->status] ?? $this->status;
    }
    
    /**
     * Gera um QR code para o equipamento
     * 
     * @param int $size Tamanho do QR code em pixels
     * @return string
     */
    public function getQrCode(int $size = 200): string
    {
        // Gerando URL para página de detalhes do equipamento
        $url = route('equipment.show', $this->serial_number);
        
        // Gerando QR code
        return \SimpleSoftwareIO\QrCode\Facades\QrCode::size($size)
            ->generate($url);
    }
    
    /**
     * Retorna a URL para visualização de detalhes do equipamento
     */
    public function getShowUrlAttribute(): string
    {
        return route('equipment.show', $this->serial_number);
    }
}
