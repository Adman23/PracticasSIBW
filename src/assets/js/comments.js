
// Event listeners para ejecutar las funciones cuando se escribe en el formulario
let fcontent = document.getElementById("fcontent");
if (fcontent) fcontent.addEventListener("keyup", function() {fcontent.value=checkBadWords(fcontent.value) })
let getAll = false;

// Se lanza cuando se carga la página entera para tener los comentarios
document.addEventListener('DOMContentLoaded', function() {
    let form = document.getElementById("comment-form");
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Evitar el envío del formulario por defecto        
            if (validateComment(event)){
                peticionAjaxComments("insert"); // Llamar a la función para enviar el comentario
                event.target.reset(); // Limpiar el formulario después de enviar
            }
        })
        // Hacemos una llamada inicial para solo cargar los comentarios
        peticionAjaxComments(); // Llamar a la función para cargar los comentarios al inicio
        console.log("Comentarios cargados");
    }
    else{
        getAll = true;
        peticionAjaxComments(null, null, getAll); // Llamar a la función para cargar los comentarios al inicio
    }
})

// Definiciones-----------------------------------------------------------------------------------
let menu = document.getElementById("comment-menu");
let prohibited_words_json =  menu.getAttribute("data-prohibitedWords");
const prohibited_words = JSON.parse(prohibited_words_json);
let user_json = menu.getAttribute("data-user");
const user = JSON.parse(user_json);

// Funciones--------------------------------------------------------------------------------------
function checkBadWords(text)
{   
    for (pw in prohibited_words)
        if(text.toLowerCase().includes(prohibited_words[pw])){
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
    const form = event.target;
    let content = form.fcontent.value; 

    let errorM = form.getElementsByClassName("error-message")[0];
    errorM.textContent = '';

    // const emailReg = new RegExp('^[a-zA-Z0-9_%+-]+@([a-zA-Z0-9-]+\.)?[a-zA-Z0-9]+\.[a-zA-Z]{2,}$');
    try{
        if(!content)
            throw new Error("Error: Es necesario que tenga algún contenido");
        
        return true;
    }
    catch(error){
        console.error("Error de validación: ", error.message);
        errorM.textContent = error.message;
    }
}


function peticionAjaxComments(order=null, commentId=null, getAll=false) {
    let commentsList = document.getElementById("comment-menu");
    let id_json = commentsList.getAttribute("data-filmId");
    let id;
    if (id_json){
        id = JSON.parse(id_json);
    }

    xhr = new XMLHttpRequest();
    if (order === "insert") {
        if (!getAll) {
            xhr.open("POST", `/ajax_comment.php?id=${id}&order=insert`, true);
        } else {
            xhr.open("POST", `/ajax_comment.php?order=insert&getAll=true`, true);
        }
    } else if (order === "delete") {
        if (!getAll) {
            xhr.open("POST", `/ajax_comment.php?id=${id}&order=delete&commentId=${commentId}`, true);
        } else {
            xhr.open("POST", `/ajax_comment.php?order=delete&commentId=${commentId}&getAll=true`, true);
        }
    } else if (order === "edit") {
        if (!getAll) {
            xhr.open("POST", `/ajax_comment.php?id=${id}&order=edit&commentId=${commentId}`, true);
        } else {
            xhr.open("POST", `/ajax_comment.php?order=edit&commentId=${commentId}&getAll=true`, true);
        }
    } else {
        if (!getAll) {
            xhr.open("GET", `/ajax_comment.php?id=${id}`, true);
        } else {
            xhr.open("GET", `/ajax_comment.php?getAll=true`, true);
        }
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
    if (order == "insert"){
        const formData = new FormData(document.getElementById('comment-form'));
        xhr.send(formData);
    }
    else if (order == "edit"){
        const formData = new FormData(document.getElementsByClassName("edit-comment-form")[0]);
        xhr.send(formData);
    }
    else{
        xhr.send();
    }
}



// Esta función recibe la respuesta del servidor y coge el JSON con
// los comentarios y lo pone en la lista de la  plantilla
function processComments(response){


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
            
            let film_name = document.createElement("a");
            let film_id = document.createElement("p");
            let commentId = document.createElement("p");

            
            author.classList.add("comment-label");
            date.classList.add("comment-label");
            text.classList.add("comment-text");

            if (!getAll){
                author.textContent = `${comment.author} (${comment.role}) / ${comment.email}`;
            }
            else{
                author.textContent = `${comment.author}`;
            }
            date.textContent = `${comment.date}`;
            text.textContent = `${comment.text}`;
            

            if (getAll){
                film_name.classList.add("comment-label");
                film_id.classList.add("comment-label");
                commentId.classList.add("comment-label");
                film_name.textContent = comment.film_name;
                film_id.textContent = comment.film_id;
                commentId.textContent = comment.id;
                film_name.href = `/pelicula/${comment.film_id}`;

                li.appendChild(commentId);
                li.appendChild(film_id);
                li.appendChild(film_name);
            }
            li.appendChild(author); 
            li.appendChild(date);     
            if (comment.edited === "1"){
                let edited = document.createElement("p");
                edited.innerHTML = '<em>Comentario editado por moderador</em>';
                li.appendChild(edited);
            }
            li.appendChild(text); 

            
            if (user.role === "superuser" 
                || user.role === "manager" 
                || user.role === "moderator"){
                let editButton = document.createElement("button");
                let deleteButton = document.createElement("button");
                
                editButton.textContent = "Editar";
                deleteButton.textContent = "Eliminar";

                editButton.classList.add("button");
                deleteButton.classList.add("button");
                editButton.classList.add("comment-button");
                deleteButton.classList.add("comment-button");

                let buttonContainer = document.createElement("div");
                buttonContainer.classList.add("button-container");
                buttonContainer.appendChild(editButton);
                buttonContainer.appendChild(deleteButton);

                editButton.active = false;
            
                deleteButton.addEventListener("click", function() {
                    peticionAjaxComments("delete", comment.id, getAll); 
                }) 

                editButton.addEventListener("click", function() {
                    if (!editButton.active){
                        let content_element = li.getElementsByClassName("comment-text")[0];
                        let content = content_element.textContent;
                        editButton.active = true;
                        editButton.oldText = content;
                        li.removeChild(content_element);

                        let new_form = document.createElement("form");
                        new_form.classList.add("edit-comment-form");
                        new_form.method = "POST";
                        let text   = document.createElement("textarea");
                        text.type  = "textarea";
                        text.name  = "fcontent";
                        text.id    = "fcontentEdit";
                        text.value = content;
                        text.required = true;
                        text.rows = 5;
                        text.cols = 40;

                        text.addEventListener("keyup", function() {text.value=checkBadWords(text.value) })

                        let errorM = document.createElement("p");
                        errorM.classList.add("error-message");


                        let submit = document.createElement("input");
                        submit.type = "submit";
                        submit.value = "Confirmar";
                        submit.classList.add("button");
                        submit.classList.add("comment-button");

                        new_form.addEventListener("submit", function(event) {
                            event.preventDefault(); // Evitar el envío del formulario por defecto        
                            if (validateComment(event)){
                                peticionAjaxComments("edit", comment.id, getAll); // Llamar a la función para enviar el comentario
                                let edit_form = li.getElementsByClassName("edit-comment-form")[0];
                                let text = document.createElement("p");
                                text.classList.add("comment-text");
                                text.textContent = `${editButton.oldText}`;
                                
                                let firstButton = li.getElementsByClassName("button-container")[0];
                                li.insertBefore(text, firstButton);  

                                li.removeChild(edit_form);
                                editButton.active = false;
                            }
                        })
                        new_form.appendChild(text);
                        new_form.appendChild(errorM);
                        new_form.appendChild(submit);

                        // Insertar el formulario antes del editButton y deleteButton
                        let firstButton = li.getElementsByClassName("button-container")[0];
                        li.insertBefore(new_form, firstButton);  
                    }
                    else{
                        let edit_form = li.getElementsByClassName("edit-comment-form")[0];
                        let text = document.createElement("p");
                        text.classList.add("comment-text");
                        text.textContent = `${editButton.oldText}`;

                        let firstButton = li.getElementsByClassName("button-container")[0];
                        li.insertBefore(text, firstButton);  
                        
                        li.removeChild(edit_form);
                        editButton.active = false;
                    }
                })
                li.appendChild(buttonContainer);
            }

            commentsList.appendChild(li);
        }   
    }
}
