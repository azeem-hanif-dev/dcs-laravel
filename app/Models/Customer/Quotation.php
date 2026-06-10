<?php

namespace App\Models\Customer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Admin;
use App\Models\Company;

class Quotation extends Model
{
    protected $fillable = ['user_id', 'company_id', 'companyname', 'date', 'address', 'validity', 'contact_person', 'phone', 'type', 'grandtotal'];
    protected $casts = ['date' => 'date', 'validity' => 'date', 'grandtotal' => 'decimal:2'];

    public function user(): BelongsTo { return $this->belongsTo(Admin::class, 'user_id'); }
    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function quoteDetails(): HasMany { return $this->hasMany(QuoteDetail::class); }
}
