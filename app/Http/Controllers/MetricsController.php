<?php

namespace App\Http\Controllers;

use App\Prometheus\Prom;
use Illuminate\Http\Request;
use Prometheus\RenderTextFormat;

class MetricsController extends Controller
{
    public function __invoke(Request $request)
    {
        $renderer = new RenderTextFormat;
        $metricSamples = Prom::getMetricFamilySamples();
        $result = $renderer->render($metricSamples);

        return response(
            $result,
            headers: ['Content-Type' => RenderTextFormat::MIME_TYPE]
        );
    }
}
