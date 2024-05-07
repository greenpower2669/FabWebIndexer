<?php
if (isset($_GET['file'])) {
    $fileName = $_GET['file'];
    $zipFileName = $fileName . '.zip';
    $apkFileName = $fileName . '.apk';

    // Vérifier si le fichier ZIP existe
    if (file_exists($zipFileName)) {
        // Vérifier l'extension
        if (pathinfo($zipFileName, PATHINFO_EXTENSION) == 'zip') {
            // Paramètres du téléchargement
            header('Content-Description: File Transfer');
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename=' . basename($zipFileName));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($zipFileName));

            // Lire le fichier ZIP et le transmettre au client
            readfile($zipFileName);
            exit;
        } else {
            echo 'Le fichier ZIP n\'a pas la bonne extension.';
        }
    }
    
    // Vérifier si le fichier APK existe
    elseif (file_exists($apkFileName)) {
        // Vérifier l'extension
        if (pathinfo($apkFileName, PATHINFO_EXTENSION) == 'apk') {
            // Paramètres du téléchargement
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.android.package-archive');
            header('Content-Disposition: attachment; filename=' . basename($apkFileName));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($apkFileName));

            // Lire le fichier APK et le transmettre au client
            readfile($apkFileName);
            exit;
        } else {
            echo 'Le fichier APK n\'a pas la bonne extension.';
        }
    } else {
        // Gérer le cas où ni le fichier ZIP ni le fichier APK n'existe
        echo 'Le fichier demandé n\'existe pas.';
    }
} else {
    // Gérer le cas où le paramètre du fichier n'est pas fourni
    echo 'Paramètre du fichier manquant.';
}
?>

