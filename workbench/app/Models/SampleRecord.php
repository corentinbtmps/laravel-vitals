<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Illuminate\Database\Eloquent\Model;

final class SampleRecord extends Model
{
    /** @var string */
    protected $table = 'sample_records';

    /** @var array<int, string> */
    protected $guarded = [];
}
