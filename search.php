<?php
require_once './vendor/autoload.php';
error_reporting(0);

$morphy = new phpMorphy('vendor/umisoft/phpmorphy/dicts/', 'ru_RU', ['storage' => PHPMORPHY_STORAGE_FILE]);
$query = $_REQUEST['query'];
$files = glob("**.html");
$is_index_updated = false;
try {
    $index = json_decode(file_get_contents('search_index.json'));
    foreach ($files as $filename) {
        if ($index->$filename->hash != hash_file('crc32', $filename)) {
            throw new Exception();
        }
    }
} catch (Exception $e) {
    $is_index_updated = true;
}

if ($is_index_updated) {
    $index = (object) [];
    foreach ($files as $filename) {
        $index->$filename = (object) [];
        $index->$filename->hash = hash_file('crc32', $filename);
        $doc = new DOMDocument();
        $doc->loadHTMLFile($filename);
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query("//body//node()[boolean(normalize-space(text()))]") as $node) {
            $path = $node->getNodePath();
            $index->$filename->$path = [];
            $text = mb_strtoupper(trim($node->textContent));
            $text = preg_replace("/\s+/", " ", $text);
            foreach (explode(' ', $text)  as $i => $word) {
                $key = $word;
                if ($lemms = $morphy->lemmatize($word)) {
                    foreach ($lemms as $lemma) {
                        if (strpos($key, $lemma) === false) {
                            $key .= " {$lemma}";
                        }
                    }
                }
                array_push($index->$filename->$path, [
                    $key,
                    $i,
                ]);
            }
        }
    }
    file_put_contents('search_index.json', json_encode($index));
}

$search_result = (object) [];
$query = mb_strtoupper($query);
foreach ($index as $filename => $file) {
    $doc = new DOMDocument();
    $doc->loadHTMLFile($filename);
    $xpath = new DOMXPath($doc);
    $search_result->$filename = (object) [];
    $search_result->$filename->title = $xpath->query("//title")[0]->textContent;
    $search_result->$filename->nodes = (object) [];
    foreach ($file as $path => $node) {

        $block = $xpath->query($path);
        $text = preg_replace("/\s+/", " ", trim($block[0]->textContent));

        $search_result->$filename->nodes->$path = (object) [];
        $search_result->$filename->nodes->$path->text = $text;
        $search_result->$filename->nodes->$path->matches = [];
        foreach ($node as $way) {
            foreach (explode(' ', $query) as $word) {
                $lemms = $morphy->lemmatize($word);
                if (!$lemms) $lemms = [];
                if (!in_array($word, $lemms)) array_push($lemms, $word);
                foreach ($lemms as $lemma) {
                    if (strpos($way[0], $lemma) !== false) {
                        array_push($search_result->$filename->nodes->$path->matches, $way[1]);
                    }
                }
            }
        }
        if (!$search_result->$filename->nodes->$path->matches) {
            unset($search_result->$filename->nodes->$path);
        }
    }
    if (!(array)$search_result->$filename->nodes) {
        unset($search_result->$filename);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/styles.css">
    <link rel="stylesheet" href="/css/owl.carousel.min.css">
    <link rel="stylesheet" href="/css/owl.theme.default.min.css">
    <link rel="stylesheet" href="/css/tachyons.min.css">
    <link rel="stylesheet" href="/css/semantic.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
    <script src="/js/jquery.min.js"></script>
    <script src="/js/semantic.min.js"></script>
    <script src="/js/owl.carousel.min.js"></script>
    <title>Магазин</title>
</head>

<body class="flex flex-column">
    <header class="flex fixed top-0 w-100 ph0 ph7-l pa0-xl items-end main-nav">
        <a class="h-100 pa2" href="/"><img class="h-100 pointer grow" class="grow" src="/images/logo.png"></a>
        <div class="dn flex-l flex-column h-100 flex-grow-1">
            <div class="flex pt3 flex-grow-1">
                <div class="ui action ml5-l input self-start" style="width: 500px">
                    <input placeholder="Футболки">
                    <button class="ui icon button blue">
                        <i class="fas fa-search white"></i>
                    </button>
                </div>
                <div class="flex flex-column ml-auto">
                    <div class="flex flex-column contacts">
                        <a class="grow no-underline pointer hover-orange" href="tel:89555555555"><i class="fas fa-phone"></i>
                            89555555555</a>
                    </div>
                    <div class="mt2">
                        <div class="grow pointer blue hover-orange" style="font-size: 20px;">
                            Заказать звонок
                        </div>
                    </div>
                </div>
            </div>
            <nav class="flex flex-wrap items-center menu">
                <div class="flex flex-wrap mb3 ml5-l">
                    <a href="/about.html" class="dim gray mh2 mb2 down">О нас</a>
                    <a href="/catalog.html" class="dim gray mh2 mb2 down">Каталог товаров</a>
                    <a href="/news.html" class="dim gray mh2 mb2 down">Новости</a>
                    <a href="/contacts.html" class="dim gray mh2 mb2 down">Контакты</a>
                </div>
            </nav>
        </div>
        <div class="flex dn-l h-100 flex-grow-1">
            <div class="ml-auto flex flex-column justify-center items-end mr4">
                <div class="flex flex-column contacts">
                    <a class="grow no-underline pointer hover-orange mb2" href="tel:89555555555"><i class="fas fa-phone"></i>
                        89555555555</a>
                    <div class="grow pointer blue hover-orange" style="font-size: 20px;">
                        Заказать звонок
                    </div>
                </div>
            </div>
            <div class="ui accordion self-center">
                <a class="title mr3"><i class="fas fa-bars gray pointer" style="font-size: 30px;"></i></a>

                <div class="content w-100 absolute left-0 ui menu" style="margin-top: 30px;">
                    <a href="/about.html" class="item dim">О нас</a>
                    <a href="/catalog.html" class="item dim">Каталог товаров</a>
                    <a href="/news.html" class="item dim">Новости</a>
                    <a href="/contacts.html" class="item dim">Контакты</a>
                    <div class="item ui input action w-100">
                        <input placeholder="Футболки">
                        <button class="ui icon button blue">
                            <i class="fas fa-search white"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main class="flex-grow-1 flex flex-column items-center mb5">
        <h1 class="dark-gray">Результаты поиска по запросу "<?php echo $_REQUEST['query']; ?>"</h1>
        <div class="w-100" style="max-width: 1000px">
            <?php foreach ($search_result as $filename => $file) : ?>
                <?php foreach ($file->nodes as $path => $node) : ?>
                    <div class="mv3">
                        <a href="/<?php echo $filename .'?go='. $path; ?>" class="grow pointer"><?php echo $file->title ?></a>
                        <p class="gray">
                            <?php
                            $words = explode(' ', $node->text);
                            foreach ($node->matches as $match) {
                                $words[$match] = '<span class="dark-gray" style="font-weight: 500;">' . $words[$match] . '</span>';
                            }
                            echo join(" ", $words);
                            ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </main>
    <footer class="flex flex-column pt4">
        <div class="flex-grow-1 flex">
            <section class="flex-grow-1 flex-basis-0 flex flex-column items-center ml3">
                <div class="flex flex-column">
                    <p class="gray mb2 grow">ООО Компания</p>
                    <p class="gray mb2 grow">89555555555</p>
                    <p class="gray mb2 grow">myemail@email.ru</p>
                    <p class="gray mb2 grow">Москва Сити, Пресненская набережная, Москва, 123317</p>
                </div>
            </section>
            <section class="flex-grow-1 flex-basis-0 flex flex-column items-center">
                <div class="flex flex-column">
                    <a href="/about.html" class="gray mb2 grow pointer">О нас</a>
                    <a href="/catalog.html" class="gray mb2 grow pointer">Каталог товаров</a>
                    <a href="/news.html" class="gray mb2 grow pointer">Новости</a>
                    <a href="/contacts.html" class="gray mb2 grow pointer">Контакты</a>
                </div>
            </section>
            <section class="flex flex-grow-1 flex-basis-0 items-start">
                <div class="mh2 grow"><img src="images/instagram.png" width="30px"></div>
                <div class="mh2 grow"><img src="images/vk.png" width="30px"></div>
                <div class="mh2 grow"><img src="images/whatsapp.png" width="30px"></div>
                <div class="mh2 grow"><img src="images/facebook.png" width="30px"></div>
            </section>
        </div>
        <hr class="w-90">
        <div class="tc white mb2 grow">
            2020 Все права защищены.
        </div>
    </footer>
</body>

</html>