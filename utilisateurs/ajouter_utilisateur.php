<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];
    $genre = $_POST['genre'];
    $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
    $telephone = mysqli_real_escape_string($conn, $_POST['telephone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = sha1($_POST['password']);
    $type_utilisateur = $_POST['type_utilisateur'];

    $sql = "INSERT INTO utilisateur (nom, prenom, date_naissance, genre, adresse, telephone, email, password, type_utilisateur) 
            VALUES ('$nom', '$prenom', '$date_naissance', '$genre', '$adresse', '$telephone', '$email', '$password', '$type_utilisateur')";

    if (mysqli_query($conn, $sql)) {
        echo "Utilisateur ajouté avec succès.";
    } else {
        echo "Erreur : " . mysqli_error($conn);
    }
}

?>

<form method="post" action="">
    <label>Nom: <input type="text" name="nom" required></label><br>
    <label>Prénom: <input type="text" name="prenom" required></label><br>
    <label>Date de naissance: <input type="date" name="date_naissance" required></label><br>
    <label>Genre: 
        <select name="genre" required>
            <option value="M">M</option>
            <option value="F">F</option>
        </select>
    </label><br>
    <label>Adresse: <textarea name="adresse"></textarea></label><br>
    <label>Téléphone: <input type="text" name="telephone"></label><br>
    <label>Email: <input type="email" name="email" required></label><br>
    <label>Mot de passe: <input type="password" name="password" required></label><br>
    <label>Type d'utilisateur: 
        <select name="type_utilisateur" required>
            <option value="Etudiant">Étudiant</option>
            <option value="Enseignant">Enseignant</option>
            <option value="Administrateur">Administrateur</option>
        </select>
    </label><br>
    <button type="submit">Ajouter</button>
</form>
