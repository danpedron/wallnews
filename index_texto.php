<?php
// Tempo de transição entre as notícias (em milissegundos)
$tempotransicao = 5000; // 5 segundos

// Carregar fontes do arquivo fonte.txt
$fontes = file('fonte.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

// Função para carregar notícias de uma fonte RSS
function carregarNoticias($url) {
    $noticias = [];
    try {
        $xml = simplexml_load_file($url);
        if ($xml && isset($xml->channel->item)) {
            foreach ($xml->channel->item as $item) {
                $noticias[] = [
                    'titulo' => (string) $item->title,
                    'link' => (string) $item->link,
                    'descricao' => (string) $item->description
                ];
            }
        }
    } catch (Exception $e) {
        // Caso a fonte não seja acessível, ignorar
    }
    return $noticias;
}

// Carregar todas as notícias das fontes cadastradas
$noticias = [];
foreach ($fontes as $fonte) {
    $noticias = array_merge($noticias, carregarNoticias($fonte));
}

// Embaralhar as notícias para variar a exibição
shuffle($noticias);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícias</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #111;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }
        .noticias {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            overflow: hidden;
        }
        .noticia {
            width: 90%;
            max-width: 1920px;
            margin: 0 auto;
            text-align: center;
            display: none;
        }
        .noticia h2 {
            font-size: 2em;
            margin: 0;
            color: #ffcc00;
            text-shadow: 2px 2px 4px #000;
        }
        .noticia p {
            font-size: 1.5em;
            margin: 10px 0;
        }
        .noticia a {
            font-size: 1.2em;
            color: #1e90ff;
            text-decoration: none;
        }
        .noticia a:hover {
            text-decoration: underline;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let tempoTransicao = <?php echo $tempotransicao; ?>;
            let noticias = document.querySelectorAll('.noticia');
            let index = 0;

            function mostrarProximaNoticia() {
                // Esconder todas as notícias
                noticias.forEach(noticia => noticia.style.display = 'none');

                // Mostrar a próxima notícia
                if (noticias.length > 0) {
                    noticias[index].style.display = 'block';
                    index = (index + 1) % noticias.length;
                }
            }

            // Alternar entre as notícias automaticamente
            if (noticias.length > 0) {
                mostrarProximaNoticia();
                setInterval(mostrarProximaNoticia, tempoTransicao);
            }
        });
    </script>
</head>
<body>
    <div class="noticias">
        <?php foreach ($noticias as $noticia): ?>
            <div class="noticia">
                <h2><?php echo htmlspecialchars($noticia['titulo']); ?></h2>
                <p><?php echo htmlspecialchars(strip_tags($noticia['descricao'])); ?></p>
                <a href="<?php echo htmlspecialchars($noticia['link']); ?>" target="_blank">Leia mais</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>