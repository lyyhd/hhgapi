<?php
/**
 * Created by PhpStorm.
 * User: zhouhaotong
 * Date: 16/1/18
 * Time: 下午9:12
 */

namespace App\Http\Controllers\Api\V1\Carousel;


use App\Http\Controllers\Api\BaseController;
use App\Models\Carousel;
use Illuminate\Http\Request;

class CarouselController extends BaseController
{
    protected $carousel;

    public function __construct(Request $request, Carousel $carousel)
    {
        parent::__construct($request);

        $this->carousel = $carousel;
    }

    //显示所有轮播
    //TODO 通过类型筛查
    public function index()
    {
        return $this->carousel->all();
    }

}