<?php
namespace Swissup\Pagespeed\Model\Css;

use Magento\Framework\App\Response\Http as ResponseHttp;

class Improver
{
    /**
     * @var \Swissup\Pagespeed\Helper\Config
     */
    private $config;

    /**
     *
     * @var \Swissup\Pagespeed\Model\Preload
     */
    private $preloader;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $ioFile;

    /**
     *
     * @var ResponseHttp|null
     */
    private $response = null;

    /**
     * @param \Swissup\Pagespeed\Helper\Config $config
     * @param \Swissup\Pagespeed\Model\Preload $preloader
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     */
    public function __construct(
        \Swissup\Pagespeed\Helper\Config $config,
        \Swissup\Pagespeed\Model\Preload $preloader,
        \Magento\Framework\Filesystem\Io\File $ioFile
    ) {
        $this->config = $config;
        $this->preloader = $preloader;
        $this->ioFile = $ioFile;
    }

    /**
     *
     * @param ResponseHttp $response
     */
    public function setResponse(ResponseHttp $response)
    {
        $this->response = $response;
        return $this;
    }

    /**
     *
     * @param string $content
     * @return string
     */
    public function process($content)
    {
        $content = (string) $content;
        $content = str_replace("\r\n", '', $content);
        $content = preg_replace('!\s+!', ' ', $content);

        $isAddFontDisplay = $this->config->isAutoAddFontDisplayForMergedCss();

        $response  = $this->response;
        $isPushFont = (bool) $response;
        if ($isAddFontDisplay || $isPushFont) {
            $fontFaces = false;
            preg_match_all('/@font-face\s*{(.*?)}/', $content, $fontFaces);

            if (isset($fontFaces[1]) && is_array($fontFaces[1])) {
                foreach ($fontFaces[1] as $fontFace) {
                    // add font-display:swap
                    // https://css-tricks.com/font-display-masses/
                    if ($isAddFontDisplay && strstr($fontFace, 'font-display:') === false) {
                        $content = str_replace($fontFace, 'font-display:swap;' . $fontFace, $content);
                    }

                    //https://developer.mozilla.org/ru/docs/Web/HTML/Preloading_content
                    /* @todo
                    <link rel="preload" href="<?= $block->getViewFileUrl('fonts/Blank-Theme-Icons/Blank-Theme-Icons.woff2')?>" as="font" type="font/woff2" crossorigin="anonymous">
                     */
                    if ($isPushFont) {
                        $fontUrls = false;
                        preg_match_all('/url\((.*)\)/imU', $fontFace, $fontUrls);
                        if (isset($fontUrls[1]) && is_array($fontUrls[1])) {
                            $preloads = [];
                            foreach ($fontUrls[1] as $fontUrl) {
                                list($fontUrl, ) = explode('?', $fontUrl, 2);
                                $fontType = 'font/' . $this->getFileExtension($fontUrl);

                                $fontUrl = str_replace('//', '/', $fontUrl);
                                if (strpos($fontUrl, '/') === 0) {
                                    $fontUrl = 'https://'  . ltrim($fontUrl, '/');
                                }
                                if (filter_var($fontUrl, FILTER_VALIDATE_URL)
                                    && $fontType === 'font/woff2'
                                ) {
                                    $preloads[] = [
                                        'as' => 'font',
                                        'href' => $fontUrl
                                    ];
                                }
                            }

                            $this->preloader->add($preloads, 'font','preload'/*,$fontType*/);
                        }
                    }
                }
            }
        }

        $exceptions = [
            'xmlns="http://www.w3.org/2000/svg"',
            "xmlns='http://www.w3.org/2000/svg'",
            'xmlns:xlink="http://www.w3.org/1999/xlink"',
            "xmlns:xlink='http://www.w3.org/1999/xlink'"
        ];
        foreach($exceptions as $fingerprint) {
            $content = str_replace($fingerprint, hash('md5', $fingerprint), $content);
        }

        $content = str_replace(['http://', 'https://'], '//', $content);

        foreach($exceptions as $fingerprint) {
            $content = str_replace(hash('md5', $fingerprint), $fingerprint, $content);
        }


        return $content;
    }

    /** instead pathinfo($path, PATHINFO_EXTENSION);
     * @param $path
     * @return string
     */
    private function getFileExtension($path)
    {
        $pathInfo = $this->ioFile->getPathInfo($path);
        return isset($pathInfo['extension']) ? $pathInfo['extension'] : false;
    }
}
