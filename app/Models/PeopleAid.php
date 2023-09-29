<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Morilog\Jalali\Jalalian;

class PeopleAid extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'product_id',
        'helper_id',
        'quantity',
        'description',
    ];

    public function aidAllocations()
    {
        return $this->hasMany(AidAllocation::class, 'people_aid_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function helper()
    {
        return $this->belongsTo(Helper::class, 'helper_id', 'id');
    }

    public static function getMonthlyCountsLastYear()
    {
        $data = [];

        $data['farvardin'] = self::whereBetween('created_at', [
            self::CarbonDate('01', '01', 'start'),
            self::CarbonDate('01', '31', 'end'),
        ])->count();

        $data['ordibehesht'] = self::whereBetween('created_at', [
            self::CarbonDate('02', '01', 'start'),
            self::CarbonDate('02', '31', 'end'),
        ])->count();

        $data['khordad'] = self::whereBetween('created_at', [
            self::CarbonDate('03', '01', 'start'),
            self::CarbonDate('03', '31', 'end'),
        ])->count();

        $data['tir'] = self::whereBetween('created_at', [
            self::CarbonDate('04', '01', 'start'),
            self::CarbonDate('04', '31', 'end'),
        ])->count();

        $data['mordad'] = self::whereBetween('created_at', [
            self::CarbonDate('05', '01', 'start'),
            self::CarbonDate('05', '31', 'end'),
        ])->count();

        $data['shahrivar'] = self::whereBetween('created_at', [
            self::CarbonDate('06', '01', 'start'),
            self::CarbonDate('06', '31', 'end'),
        ])->count();

        $data['mehr'] = self::whereBetween('created_at', [
            self::CarbonDate('07', '01', 'start'),
            self::CarbonDate('07', '30', 'end'),
        ])->count();

        $data['aban'] = self::whereBetween('created_at', [
            self::CarbonDate('08', '01', 'start'),
            self::CarbonDate('08', '30', 'end'),
        ])->count();

        $data['azar'] = self::whereBetween('created_at', [
            self::CarbonDate('09', '01', 'start'),
            self::CarbonDate('09', '30', 'end'),
        ])->count();

        $data['dey'] = self::whereBetween('created_at', [
            self::CarbonDate('10', '01', 'start'),
            self::CarbonDate('10', '30', 'end'),
        ])->count();

        $data['bahman'] = self::whereBetween('created_at', [
            self::CarbonDate('11', '01', 'start'),
            self::CarbonDate('11', '30', 'end'),
        ])->count();

        $data['esfand'] = self::whereBetween('created_at', [
            self::CarbonDate('12', '01', 'start'),
            self::CarbonDate('12', '29', 'end'),
        ])->count();

        return array_values($data);
    }

    private static function CarbonDate($month, $day, $hour)
    {
        if ($hour == 'start') {
            return Jalalian::fromFormat('Y-m-d H:i', ''. Jalalian::fromCarbon(Carbon::now())->getYear() .'-'. $month .'-'. $day .' 00:00')->toCarbon();
        } else {
            return Jalalian::fromFormat('Y-m-d H:i', ''. Jalalian::fromCarbon(Carbon::now())->getYear() .'-'. $month .'-'. $day .' 23:59')->toCarbon();
        }
    }
}
