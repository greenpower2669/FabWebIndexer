<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Images FabDoubleFinder</title>
    <style>
        body {
            background-color: black;
            width: 100%;
            color: white;
            text-align: center;
            overflow: hidden;
            margin: 0;
        }

        .scroll-container {
            height: 80vh; /* Ajustez la hauteur de la zone de défilement selon vos besoins */
            overflow-y: auto;
        }

        .image-container {
            width: 80%;
            margin: 10vh auto; /* 10vh pour une marge de 10% de la hauteur de l'écran */
            position: relative;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .media {
            width: 50%; /* Définir la largeur à 50% de la largeur du conteneur */
            height: auto;
            border: 1px solid white;
            position: relative;
            z-index: 1; /* Assurez-vous que la vidéo est devant l'overlay */
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 50%;
            height: 50%;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            pointer-events: none;
            z-index: 2; /* Mettez l'overlay au-dessus de la vidéo et de tout le reste */
        }

        .image-container:hover {
            transform: scale(1.05);
        }

        .image-name {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(200, 200, 200, 0.8); /* Fond gris clair semi-transparent */
            color: green;
            font-size: 14px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            border-radius: 10px; /* Cadre arrondi */
            padding: 5px; /* Espacement intérieur */
            transform: translateZ(10px); /* Effet 3D */
               z-index: 2; /* Mettez l'overlay au-dessus de la vidéo et de tout le reste */
        }
    </style>
</head>
<body>

<div class="scroll-container">

    <?php
    $imageDirectory = './'; // Utilisez le chemin approprié si nécessaire

    $mediaFiles = glob($imageDirectory . '*.{png,mp4}', GLOB_BRACE);

    foreach ($mediaFiles as $mediaFile) {
        $fileName = pathinfo($mediaFile, PATHINFO_FILENAME);

        echo '<div class="image-container" onmousemove="moveOverlay(event, \'' . $fileName . '\')" onmouseleave="hideOverlay(\'' . $fileName . '\')">';
        echo '<div class="overlay" id="overlay-' . $fileName . '">' . file_get_contents($fileName . '.txt') . '</div>';
        echo '<div class="image-name">' . $fileName . '</div>';

        // Utilisation de la balise <video> si le fichier est une vidéo
        if (pathinfo($mediaFile, PATHINFO_EXTENSION) == 'mp4') {
            echo '<video class="media" autoplay loop muted playsinline>';
            echo '<source src="' . $mediaFile . '" type="video/mp4">';
            echo 'Your browser does not support the video tag.';
            echo '</video>';
        } else {
            // Utilisation de la balise <img> si le fichier est une image
            echo '<a href="download.php?file=' . urlencode($fileName) . '"><img class="media" src="' . $mediaFile . '" alt="Media"></a>';
        }

        echo '</div>';
    }
    ?>

</div>

<script>
    function moveOverlay(event, fileName) {
        const overlay = document.getElementById('overlay-' + fileName);
        const x = event.clientX;
        const y = event.clientY;

        overlay.style.left = x + 'px';
        overlay.style.top = y + 'px';
        overlay.style.display = 'flex';
    }

    function hideOverlay(fileName) {
        const overlay = document.getElementById('overlay-' + fileName);
        overlay.style.display = 'none';
    }
</script>

</body>
</html>
