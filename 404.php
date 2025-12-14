<?php

require_once 'cicek.php';
require_once 'header.php';

echo '<div class="hosgeldin">';
echo '<h1>' . ($dil == 'tr' ? 'Sayfa Bulunamadı' : 'Page Not Found') . '</h1>';
echo '<p>' . ($dil == 'tr' ? 'Aradığınız sayfa bulunamadı. Ana sayfaya dönmek için aşağıdaki butonu kullanabilirsiniz.' : 'The page you are looking for was not found. You can use the button below to return to the home page.') . '</p>';
echo '<a href="?sayfa=anasayfa" class="odeme-btn" style="width: auto; display: inline-block; padding: 12px 30px;">' . ($dil == 'tr' ? 'Ana Sayfaya Dön' : 'Return to Home') . '</a>';
echo '</div>';
require_once 'footer.php';
?>