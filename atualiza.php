<?php
function carregarNoticias($url) {
    $noticias = [];
    try {
        $xml = simplexml_load_file($url);
        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $imagem = null;

                if (isset($item->children('media', true)->content)) {
                    $imagem = (string) $item->children('media', true)->content->attributes()->url;
                } elseif (isset($item->enclosure)) {
                    $imagem = (string) $item->enclosure->attributes()->url;
                }

                $noticias[] = [
                    'titulo' => (string) $item->title,
                    'link' => (string) $item->link,
                    'imagem' => $imagem
                ];
            }
        }
    } catch (Exception $e) {}
    return $noticias;
}

$fontes = file('fonte.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$noticias = [];
foreach ($fontes as $fonte) {
    $noticias = array_merge($noticias, carregarNoticias($fonte));
}
shuffle($noticias);

header('Content-Type: application/json');
echo json_encode($noticias);
