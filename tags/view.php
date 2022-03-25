<!DOCTYPE html>
<html>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/head.php'; ?>
    <body class="background">
        <header>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/header.php'; ?>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/subMenuNav.php'; ?>
        </header>
        <main>
            <div id='opacity'></div>
            <?php if($action == 'tags') : ?>
            <?php
                if($subAction == 'newTag'){
                    echo '<div class="overlay">';
                    include $_SERVER['DOCUMENT_ROOT'] . '/module/newTag.php';
                    echo '</div>';
                }
                elseif($subAction == 'newTagEx'){
                    echo '<div class="overlay">';
                    include $_SERVER['DOCUMENT_ROOT'] . '/module/newTagEx.php';
                    echo '</div>';
                }
                elseif($subAction == 'printTag'){
                    echo '<div class="overlay">';
                    include $_SERVER['DOCUMENT_ROOT'] . '/module/printCompTag.php';
                    echo '</div>';
                }
                elseif($subAction == 'reprintTag'){
                    echo '<div class="overlay">';
                    include $_SERVER['DOCUMENT_ROOT'] . '/module/modifyTag.php';
                    echo '</div>';
                }
                elseif($subAction == 'deactivateTag'){
                    echo '<div class="overlay">';
                    include $_SERVER['DOCUMENT_ROOT'] . '/module/modifyTag.php';
                    echo '</div>';
                }
                ?>
            
            <a href= "/logIn/index.php?action=mainMenu" id="exit"><span>x</span></a>
                <form method="post" action="." id="mainMenu">
                    <h1>Load Tag menu</h1>
                    <?php if($message){echo'<li><h3>' . $message . '</h3></li>';}?>
                        <ul>
                            <li><button type="submit" name="action" id="action" value="newTag" title='New Tag'><span>+</span></button>
                            <li><button type="submit" name="action" id="action" value="modifyTag" title='Modify Tag'><span>p</span></button>
                            <li><button type="submit" name="action" id="action" value="reprintTag" title='Reprint Tag'><span>n</span></button>
                            <li><button type="submit" name="action" id="action" value="testPrint" title='Delete Tag'><span>X</span></button>
                        </ul>
                </form>
            <?php else : ?>
                <?php header('/logIn/Index.php'); ?>       
            <?php endif ?>
        </main>
        <footer>
            <?php include $_SERVER['DOCUMENT_ROOT'] . '/module/footer.php'; ?>
        </footer>    
    </body>
</html>

