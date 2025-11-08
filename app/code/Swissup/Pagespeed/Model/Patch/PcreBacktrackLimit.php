<?php
namespace Swissup\Pagespeed\Model\Patch;

class PcreBacktrackLimit implements PatcherInterface
{
    private $prevLimit = false;

    public function apply(?\Magento\Framework\App\Response\Http $response = null)
    {
        $html = (string) $response->getBody();
        if (empty($html)) {
            return;
        }
        $hasSriHashes = strpos($html, 'window.sriHashes = ') !== false;
        $limit = (int) ini_get('pcre.backtrack_limit');
        $hasTooLongScripts = $setPcreBacktrackLimit = false;
        if ($hasSriHashes) {
            $maxScriptLength = $this->getMaxTagContentsLength($html, 'script');
            $hasTooLongScripts = $maxScriptLength > $limit;
            if ($hasTooLongScripts) {
                $newLimit = round($maxScriptLength + 500, -3);
                $setPcreBacktrackLimit = ini_set('pcre.backtrack_limit', (string) $newLimit);
            }
        }

        $this->prevLimit = $setPcreBacktrackLimit ? $limit : false;
    }

    public function restore()
    {
        if ($this->prevLimit) {
            ini_set('pcre.backtrack_limit', (string) $this->prevLimit);
        }
    }

    private function getMaxTagContentsLength($html, $tag = 'script')
    {
        $maxLength = 0;
        $offset = 0;

        while (($startPosition = strpos($html, '<' . $tag, $offset)) !== false) {
            $startCloseTagPosition = strpos($html, '>', $startPosition);
            if ($startCloseTagPosition === false) {
                break;
            }

            $endPosition = strpos($html, '</' . $tag . '>', $startCloseTagPosition);
            if ($endPosition === false) {
                break;
            }

            $contentStart = $startCloseTagPosition + 1;
            $contentLength = $endPosition - $contentStart;

            if ($contentLength > $maxLength) {
                $maxLength = $contentLength;
            }

            $offset = $endPosition + strlen('</' . $tag . '>');
        }

        return $maxLength;
    }
}
