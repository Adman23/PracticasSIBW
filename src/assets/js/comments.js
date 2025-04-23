
// Event listeners para ejecutar las funciones cuando se escribe en el formulario
let fname = document.getElementById("fname");
fname.addEventListener("keyup", function() {fname.value=checkBadWords(fname.value) })

let femail = document.getElementById("femail");
femail.addEventListener("keyup", function() {femail.value=checkBadWords(femail.value) })

let fcontent = document.getElementById("fcontent");
fcontent.addEventListener("keyup", function() {fcontent.value=checkBadWords(fcontent.value) })


// Se lanza cuando se carga la página entera para tener los comentarios
document.addEventListener('DOMContentLoaded', function() {
    let form = document.getElementById("comment-form");
    form.addEventListener("submit", function(event) {
        event.preventDefault(); // Evitar el envío del formulario por defecto        
        if (validateComment(event)){
            peticionAjax(true); // Llamar a la función para enviar el comentario
            event.target.reset(); // Limpiar el formulario después de enviar
        }
    })

    // Hacemos una llamada inicial para solo cargar los comentarios
    peticionAjax(false); // Llamar a la función para cargar los comentarios al inicio
    console.log("Comentarios cargados");
})

// Definiciones-----------------------------------------------------------------------------------
let menu = document.getElementById("comment-menu");
let prohibited_words_json =  menu.getAttribute("data-prohibitedWords");
const prohibited_words = JSON.parse(prohibited_words_json);

// Funciones--------------------------------------------------------------------------------------
function checkBadWords(text)
{   
    for (pw in prohibited_words)
        if(text.includes(prohibited_words[pw])){
            text = text.replace(new RegExp(prohibited_words[pw], 'i'), '*'.repeat(prohibited_words[pw].length));
        }
    return text;
}

function showMenu()
{
    menu.classList.toggle("active-comments-menu");
}

// Función para validar el comentario (Se ejecuta antes de postComment)
function validateComment(event)
{
    let errorM = document.getElementById("error-message");
    errorM.textContent = '';
    const emailReg = new RegExp('^[a-zA-Z0-9_%+-]+@([a-zA-Z0-9-]+\.)?[a-zA-Z0-9]+\.[a-zA-Z]{2,}$');
    try{
        const form = event.target;
        let name = form.fname.value;
        let email = form.femail.value;
        let content = form.fcontent.value; 

        
        if(!name)
            throw new Error("Error: El campo de nombre es obligatorio");
        if(!email)
            throw new Error("Error: El campo de e-mail es obligatorio");
        if (!emailReg.test(email))
            throw new Error("El email no tiene un formato correcto");
        if(!content)
            throw new Error("Error: Es necesario que tenga algún contenido");
        
        return true;
    }
    catch(error){
        console.error("Error de validación: ", error.message);
        errorM.textContent = error.message;
    }
}

// Esta función gestiona la petición AJAX, es decir, utiliza el archivo
// php para recoger datos de la base de datos
function peticionAjax(insert) {
    // Obtenemos id de pelicula desde la plantilla
    let commentsList = document.getElementById("comment-menu");
    let id_json = commentsList.getAttribute("data-filmId");
    let id = JSON.parse(id_json);
    const formData = new FormData(document.getElementById('comment-form'));

    xhr = new XMLHttpRequest();

    if (insert){
        xhr.open("POST", `/ajax_comment.php?id=${id}`, true);
    }
    else{
        xhr.open("GET", `/ajax_comment.php?id=${id}`, true);
    }
    
    
    // Lanzará función de procesado CUANDO SE COMPLETE 
    xhr.onload = function() {
        if (xhr.status === 200) {
            processComments(xhr.response);  
        } else {
            console.error('Error:', xhr.statusText);
        }
    }

    // Manda la solicitud
    if (insert){
        xhr.send(formData);
    }
    else{
        xhr.send();
    }
}



// Esta función recibe la respuesta del servidor y coge el JSON con
// los comentarios y lo pone en la lista de la  plantilla
function processComments(response){

    console.log("Comentarios recibidos: ", response);
    let comments = JSON.parse(response);
    let commentsList = document.getElementById("comment-list");
    commentsList.innerHTML = "";

    if (comments.length != 0){
        for (let i = 0; i < comments.length; i++) {
            let comment = comments[i];
            let li = document.createElement("li");
            li.classList.add("comment");
            
            let author = document.createElement("p");
            let date = document.createElement("p");
            let text = document.createElement("p");
            
            author.classList.add("comment-label");
            date.classList.add("comment-label");
            text.classList.add("comment-text");

            author.textContent = `${comment.author} / ${comment.email}`;
            date.textContent = `${comment.date}`;
            text.textContent = `${comment.text}`;

            li.appendChild(author);
            li.appendChild(date);
            li.appendChild(text);

            commentsList.appendChild(li);
        }   
    }
}
