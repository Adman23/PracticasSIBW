
// Primeros event listeners-----------------------------------------------------------------------------------
let getAll = false;
// Event listener para controlar lo que se escribe en un nuevo comentario
let fcontent = document.getElementById("fcontent");
if (fcontent) fcontent.addEventListener("keyup", function() {fcontent.value=checkBadWords(fcontent.value) })

// Se lanza cuando se carga la página entera para tener los comentarios
document.addEventListener('DOMContentLoaded', function() {
    const formSearch = document.getElementsByClassName("search-form")[0] ?? null;
    const search = formSearch ? formSearch.querySelector("input[name='q']") : null;
    if (search) search.addEventListener("keyup", function (){
        peticionAjaxComments(null, null, getAll);
    })

    let form = document.getElementById("comment-form"); // Esto es en caso de que esté el formulario de comentarios
    if (form) {
        form.addEventListener("submit", function(event) {
            event.preventDefault(); // Evitar el envío del formulario por defecto      
            peticionAjaxComments("insert"); // Llamar a la función para enviar el comentario
            event.target.reset(); // Limpiar el formulario después de enviar
        })
        // Hacemos una llamada inicial para solo cargar los comentarios
        peticionAjaxComments(); // Llamar a la función para cargar los comentarios al inicio
    }
    else{
        // Se cargan los comentarios del inicio, la diferencia está en que getAll lo hace general de todas las películas
        getAll = true;
        peticionAjaxComments(null, null, getAll);
    }
})



// Definiciones-----------------------------------------------------------------------------------
let menu = document.getElementById("comment-menu");
let prohibited_words_json =  menu.getAttribute("data-prohibitedWords");
const prohibited_words = JSON.parse(prohibited_words_json);
let user_json = menu.getAttribute("data-user");
const user = JSON.parse(user_json);

// Funciones--------------------------------------------------------------------------------------
// Se usa por el event listener del principio para hacer la verificación de palabras malas
function checkBadWords(text)
{   
    for (pw in prohibited_words)
        if(text.toLowerCase().includes(prohibited_words[pw])){
            text = text.replace(new RegExp(prohibited_words[pw], 'i'), '*'.repeat(prohibited_words[pw].length));
        }
    return text;
}


// Función para mostrar o no el menú de comentarios (específico de una película)
function showMenu()
{
    menu.classList.toggle("active-comments-menu");
}


// Función de validación del comentario (Se ejecuta antes de petición Ajax)
// DEPRECATED --> Ya solo se comprueba el texto y eso lo controla el propio formulario
function validateComment(event)
{
    const form = event.target;
    let content = form.fcontent.value; 

    let errorM = form.getElementsByClassName("error-message")[0];
    errorM.textContent = '';

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


// Petición ajax de los comentarios, utiliza el archivo ./ajax_comment.php de manera directa
// para no pasar por index, y usa varios atributos distintos para sacar distinta funcionalidad
function peticionAjaxComments(order=null, commentId=null, getAll=false) {
    let commentsList = document.getElementById("comment-menu");
    let id_json = commentsList.getAttribute("data-filmId");
    let id;
    if (id_json){
        id = JSON.parse(id_json);
    }

    // Uso XML request
    xhr = new XMLHttpRequest();
    let url = "/ajax_comment.php";
    let params = []; // Los parámetros con los que se lanza

    if (!getAll && id) params.push(`id=${id}`);
    if (order) params.push(`order=${order}`);
    if (commentId) params.push(`commentId=${commentId}`);
    if (getAll) params.push("getAll=true");

    // Crea la url final metiendo los parámetros separados por &
    if (params.length > 0) url += "?" + params.join("&"); 

    xhr.open("POST", url, true);

    // Lanzará función de procesado CUANDO SE COMPLETE 
    xhr.onload = function() {
        if (xhr.status === 200) {
            processComments(xhr.response);  
        } else {
            console.error('Error:', xhr.statusText);
        }
    }

    
    const formDataSearch = new FormData(document.getElementsByClassName("search-form")[0]) ?? {};

    // Manda la solicitud
    if (order == "insert"){
        const formData = new FormData(document.getElementById('comment-form'));
        for (const [key, value] of formDataSearch.entries()) {
            formData.append(key, value);
        }
        xhr.send(formData)
    }
    else if (order == "edit"){
        const formData = new FormData(document.getElementsByClassName("edit-comment-form")[0]);
        for (const [key, value] of formDataSearch.entries()) {
            formData.append(key, value);
        }
        xhr.send(formData);
    }
    else{
        xhr.send(formDataSearch);
    }
}


// Función principal que crea y edita el html con los datos que se le pasan desde el servidor
function processComments(response){

    let comments = JSON.parse(response);
    let commentsList = document.getElementById("comment-list");
    commentsList.innerHTML = ""; // Borramos todo lo que había

    if (comments.length != 0){
        for (let i = 0; i < comments.length; i++) {
            const li = createComment(comments[i]);  // Para cada uno de los comentarios
                                                    // que hay como respuesta
            commentsList.appendChild(li);
        }   
    }
}


// Crea un comentario a partir de los datos de "comment"
function createComment(comment) {
    // CREACIÓN DE ELEMENTOS----------------------------------------------------------
    let li = document.createElement("li"); // Elemento principal al que añadimos todo
    li.classList.add("comment");
    
    // Creamos los elementos html del comentario
    let author = document.createElement("span");
    let date = document.createElement("span");
    let text = document.createElement("p");
        
    // Añadimos las clases pertinentes (unas son para la lista de comentarios
    // otras son para el comentario cuando se muestra en una película específica)
    author.classList.add("list-cell");
    date.classList.add("list-cell");
    text.classList.add("list-cell");

    author.classList.add("comment-label");
    date.classList.add("comment-label");
    text.classList.add("comment-text");

    // Modificamos el contenido (de author es más abajo)
    date.textContent = `${comment.date}`;
    text.textContent = `${comment.text}`;   


    // En caso de que haya que obtener todas las películas
    if (getAll){
        // Creamos los elementos pertinentes
        let film_name = document.createElement("span");
        let film_name_link = document.createElement("a");

        let film_id = document.createElement("span");
        let commentId = document.createElement("span");

        // Añadimos las clases de lista 
        film_name.classList.add("list-cell");
        film_id.classList.add("list-cell");
        commentId.classList.add("list-cell");

        // Editamos el contenido
        film_name_link.textContent = comment.film_name;
        film_name_link.href = `/pelicula/${comment.film_id}`;

        film_id.textContent = comment.film_id;
        commentId.textContent = comment.id;
        
        // Este es para el usuario que ha puesto el comentario, cuando
        // son todas las películas se acorta de esta forma, en caso contrario
        // está el "else"
        author.textContent = `${comment.author}`;

        // Añadimos los elementos (van antes que los otros)
        film_name.append(film_name_link);
        li.appendChild(commentId);
        li.appendChild(film_name);
        li.appendChild(film_id);
    }
    else {
        // En caso de que no sean todas las películas
        author.textContent = `${comment.author} (${comment.role}) / ${comment.email}`;
    }


    // Este elemento es para indicar cuando un comentario ha sido editado
    // En la lista general si es que no ha sido editado sale la línea
    let edited = document.createElement("p"); 
    edited.classList.add("list-cell");

    if (getAll && comment.edited === "1"){
        edited.innerHTML = '<em>Editado</em>';
    }
    else if (getAll) {
        edited.innerHTML = '<em>-------</em>';
    }
    else if (comment.edited === "1"){
        edited.innerHTML = '<em>Comentario editado por moderador</em>';
    }


    // Estos se añaden siempre
    li.appendChild(author); 
    li.appendChild(date);  
    if (comment.edited === "1" || getAll) li.appendChild(edited);
    li.appendChild(text); 
    
    // -------------------------------------------------------------------------------
    
    // CREACIÓN DE LOS BOTONES PARA LOS USUARIOS DE ADMINISTRACIÓN
    if (user.role === "superuser" || user.role === "manager" || user.role === "moderator"){
        // Creamos los botones de edición
        let buttonContainer = document.createElement("div");
        let editButton = document.createElement("button");
        let deleteButton = document.createElement("button");
        
        // Clases
        buttonContainer.classList.add("button-container");
        buttonContainer.classList.add("list-cell");

        editButton.classList.add("button");
        editButton.classList.add("comment-button");

        deleteButton.classList.add("button");
        deleteButton.classList.add("comment-button");
        
        // Contenido
        editButton.textContent = "Editar";
        deleteButton.textContent = "Eliminar";

        // Appends
        buttonContainer.appendChild(editButton);
        buttonContainer.appendChild(deleteButton);

        // Este false sirve para controlar la apertura del cuadro de texto
        // de edición del comentario
        editButton.active = false;
    
        deleteButton.addEventListener("click", function() {
            peticionAjaxComments("delete", comment.id, getAll); 
        }) 

        editButton.addEventListener("click", function() {
            if (!editButton.active){
                // Obtenemos el contenedor del texto y guardamos su contenido
                let content_element = li.getElementsByClassName("comment-text")[0];
                let content = content_element.textContent;
                editButton.active = true; // ponemos el active
                editButton.oldText = content;
                li.removeChild(content_element);    // Después de guardar el contenido
                                                    // borramos el elemento

                // Creamos el formulario de edición
                let new_form = document.createElement("form");
                new_form.classList.add("edit-comment-form");
                new_form.method = "POST";
                
                // Creamos el textarea con el contenido antiguo para que se pueda editar
                // los parámetros son los que tiene el textea de los comentarios (el otro form)
                let text   = document.createElement("textarea");
                text.type  = "textarea";
                text.name  = "fcontent";
                text.id    = "fcontentEdit";
                text.value = content;
                text.required = true;
                text.rows = 5;
                text.cols = 40;

                // Le añadimos el addEventListener de palabras malas
                text.addEventListener("keyup", function() {text.value=checkBadWords(text.value) })

                /* No es realmente necesario porque no estamos mostrando errores (ya que no
                    tenemos el validateComment ahora mismo)*/
                let errorM = document.createElement("p");
                errorM.classList.add("error-message");

                // Creamos botón de submit
                let submit = document.createElement("input");
                submit.type = "submit";
                submit.value = "Confirmar";
                submit.classList.add("button");
                submit.classList.add("comment-button");

                // Le damos función al submit
                new_form.addEventListener("submit", function(event) {
                    event.preventDefault(); // Evitar el envío del formulario por defecto        
                    peticionAjaxComments("edit", comment.id, getAll); // Llamar a la función para editar el comentario
                    // No hace falta hacer nada más porq peticiónAjax comment
                    // vuelve a cargar los comentarios de nuevo (está editando uno)
                })

                // Metemos los contenidos en el formulario
                new_form.appendChild(text);
                new_form.appendChild(errorM);
                new_form.appendChild(submit);

                // Insertar el formulario antes del editButton y deleteButton
                let buttonContainer = li.getElementsByClassName("button-container")[0];
                li.insertBefore(new_form, buttonContainer);  
            }
            else{ // En este caso volvemos a lo que teniamos antes un "rollback"
                let edit_form = li.getElementsByClassName("edit-comment-form")[0];

                // Volvemos a crear el text
                let text = document.createElement("p");
                text.classList.add("list-cell");
                text.classList.add("comment-text");
                text.textContent = `${editButton.oldText}`;

                // Colocamos el texto encima de los botones
                let buttonContainer = li.getElementsByClassName("button-container")[0];
                li.insertBefore(text, buttonContainer);  
                
                // Eliminamos el formulario de edición
                li.removeChild(edit_form);
                // Volvemos a activar que se pueda abrir
                editButton.active = false;
            }
        })

        li.appendChild(buttonContainer);
    }


    return li;
}