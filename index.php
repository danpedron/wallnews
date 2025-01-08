<?php
$tempotransicao = 20000; // 20 segundos

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
        .noticia img {
            height: 80vh; /* Altura fixa em 80% da altura da tela */
            width: auto; /* Ajusta a largura proporcionalmente */
            object-fit: cover; /* Amplia ou reduz mantendo proporções */
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
        }
        .noticia h2 {
            font-size: 3em;
            margin: 0;
            color: #ffcc00;
            text-shadow: 2px 2px 4px #000;
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
                noticias.forEach(noticia => noticia.style.display = 'none');
                if (noticias.length > 0) {
                    noticias[index].style.display = 'block';
                    index = (index + 1) % noticias.length;
                }
            }

            function atualizarNoticias() {
                fetch('atualizar.php')
                    .then(response => response.json())
                    .then(data => {
                        const container = document.querySelector('.noticias');
                        container.innerHTML = ''; // Limpar as notícias atuais

                        data.forEach(noticia => {
                            const div = document.createElement('div');
                            div.className = 'noticia';

                            if (noticia.imagem) {
                                const img = document.createElement('img');
                                img.src = noticia.imagem;
                                img.alt = 'Imagem da notícia';
                                div.appendChild(img);
                            }

                            const h2 = document.createElement('h2');
                            h2.textContent = noticia.titulo;
                            div.appendChild(h2);

                            const a = document.createElement('a');
                            a.href = noticia.link;
                            a.target = '_blank';
                            a.textContent = 'Leia mais';
                            div.appendChild(a);

                            container.appendChild(div);
                        });

                        // Atualizar lista de notícias
                        noticias = document.querySelectorAll('.noticia');
                        index = 0;
                        mostrarProximaNoticia();
                    })
                    .catch(error => console.error('Erro ao atualizar notícias:', error));
            }

            if (noticias.length > 0) {
                mostrarProximaNoticia();
                setInterval(mostrarProximaNoticia, tempoTransicao);
            }

            // Atualizar as notícias a cada 30 minutos (1800000ms)
            setInterval(atualizarNoticias, 1800000);
        });
    </script>
</head>
<body>
    <div class="noticias">
        <?php foreach ($noticias as $noticia): ?>
            <div class="noticia">
                <?php if (!empty($noticia['imagem'])): ?>
                    <img src="<?php echo htmlspecialchars($noticia['imagem']); ?>" alt="Imagem da notícia">
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($noticia['titulo']); ?></h2>
                <a href="<?php echo htmlspecialchars($noticia['link']); ?>" target="_blank">Leia mais</a>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
