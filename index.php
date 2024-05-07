<!DOCTYPE html>
<html>
<head>
	<title>Liste des répertoires</title>
	<style>
		       @keyframes pulseF {
            0% {
                
                background: linear-gradient(45deg, rgb(255, 213, 0), rgb(51, 255, 252), rgb(248, 247, 247)) ;
            }
            50% {
              
              background: linear-gradient(45deg, rgb(248, 247, 247), rgb(255, 213, 0), rgb(51, 255, 252)) ;
            }
            100% {
              background: linear-gradient(45deg, rgb(51, 255, 252), rgb(248, 247, 247), rgb(255, 213, 0)) ;
                
            }
         }
		    
		 #footerb {
        scale: 0.6;
              border: 2px solid;
              border-radius: 44px;
              border-color: #1c1ce5;
              z-index: 9999999999;
            
                  width: 120px;
                  background: linear-gradient(45deg, rgb(0, 238, 255), rgb(78, 255, 51), rgb(212, 0, 255));
                  
                  text-align: center;
                  animation: pulseF .5s infinite;
		 }
		.my-block {
		  background-color: black;
		  width: 40rem;
		  height: 40rem;
		  position: absolute;
		  top: 50%;
		  left: 50%;
		  transform: translate(-50%, -50%);
		  cursor: url("pt.gif"), auto;
		}

		.my-list {
			list-style-type: none;
			padding: 0;
			margin: 0;
			display: flex;
			flex-direction: column;
			align-items: center;
			justify-content: center;
			height: 100%;
		}

		.my-list-item {
			margin: 1rem;
			font-size: 2rem;
			color: white;
			text-align: center;
			text-decoration: none;
			border: 1px solid white;
			padding: 1rem;
			width: 100%;
			
			
		}
		.item:hover {
			color: black;
		
	}
		.item:link {
		
			color: white; /* Forcer la couleur à être blanche */
			text-decoration: none;
		}
		.item:visited {
    color: white; /* Forcer la couleur  */
    text-decoration: none;
}

		.my-list-item:hover {
			background-color: white;
			color: black;
		}
	.my-list-item:hover .item:link {
			color: black;
		
	}
	.my-list-item:hover .item:visited {
			color: black;
		
	}
	.my-list-item:hover .item {
			color: black;
		
	}
	.my-list-item:hover .item:link,
.my-list-item:hover .item:visited {
    color: black; /* Couleur des liens non visités et visités */
}
	</style>
</head>
<body class="my-block">
	<div >
		<?php
        $dir = "."; // Le répertoire actuel, vous pouvez le changer si nécessaire
        $dirs = array_filter(scandir($dir), function($item) use ($dir) {
            return is_dir($dir . DIRECTORY_SEPARATOR . $item) && !in_array($item, ['.', '..']);
        });

        foreach($dirs as $directory) {
            // Construisez le chemin complet du fichier GIF associé au répertoire
            $gifPath = $dir . DIRECTORY_SEPARATOR . $directory . '.gif';

            echo '<li class="my-list-item">';
			if (file_exists($gifPath)) {
		 		  
				echo '<img id="footerb" src="' . $gifPath . '" alt="GIF">';
				   }
            echo '<a class="item" href="' . $directory . '" target="_blank">' . $directory . '</a>';
            
            // Vérifiez si le fichier GIF existe et affichez-le si c'est le cas
            if (file_exists($gifPath)) {
		 		  
		 echo '<img id="footerb" src="' . $gifPath . '" alt="GIF">';
            }
            
            echo '</li>';
        }
        ?>

<ul>

</ul>

		?>
	</div>
</body>
<scripr>
const listItems = document.querySelectorAll('.my-list-item');

listItems.forEach(item => {
  item.addEventListener('click', (event) => {
    const dirName = event.target.textContent.trim();
    const baseUrl = window.location.origin + window.location.pathname;
    const url = baseUrl + '/' + dirName;
    window.open(url);
  });
});

</script>
</html>
