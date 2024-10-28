<?php

class QRMaker
{

    public function __construct(){}

    public function createQRCode($url)
    {
        ob_start();
        QRcode::png($url, null);
        $imageData = ob_get_contents();
        ob_end_clean();

        return 'data:image/png;base64,' . base64_encode($imageData);
    }
}