<?php

namespace App\Models\Admin;

use App\Models\Admin\Concerns\SessionModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HawanSession extends Model
{
    use SessionModel, SoftDeletes;
}
