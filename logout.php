<?php
/**
 * Page de déconnexion - Application IPIRNET
 */

session_start();

// Destruction de la session
session_destroy();

// Redirection vers la page de connexion avec message
session_start();
$_SESSION['success_message'] = 'Vous avez été déconnecté avec succès.';

header('Location: login.php');
exit();
?>
