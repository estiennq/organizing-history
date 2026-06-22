async function verifyStudent(studentId) {

    /*let url = window.location.href + "/verify";


    let data = {
        studentId: studentId,
    };
    /*let response = await fetch("/admin/students/verify", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    })*/

    /*let requestOptions = {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(data),
    };
    console.log(data);
    console.log(url);

    fetch("/admin/students/verify", requestOptions)
        .then(response => response.json())
        .catch(error => {
            // Gestion des erreurs ici
            console.error('Erreur de requête :', error);

            // Vérifier si l'erreur est une instance de Response
            if (error instanceof Response) {
                // Récupérer le texte de la réponse
                return error.text();
            } else {
                // Si ce n'est pas une instance de Response, afficher l'erreur directement
                console.error('Erreur non gérée :', error);
            }
        })
        .then(errorMessage => {
            console.log('Réponse complète du serveur :', errorMessage);
        });*/
}