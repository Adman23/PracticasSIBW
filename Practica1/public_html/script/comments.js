

class Comment {
    constructor(autor, date, text)
    {
        this.autor = autor;
        this.date = date;
        this.text = text;
    }
}

function showMenu()
{
    menu = document.getElementById("comment-menu");
    menu.classList.toggle("active-comments-menu");
}


// Creamos los comentarios iniciales
let comment_list = [];

// Coloca en el html el ultimo comentario de la lista
function postComment()
{
    comment = comment_list[comment_list.length-1];
    // Obtenemos el listado
    list = document.getElementById("comment-list");

    // Creamos un elemento de listado nuevo
    new_element = document.createElement("li");
    new_element.classList.add("comment");

    // Creamos los subelementos que tendrá cada elemento de la lista
    autor = document.createElement("p");
    date = document.createElement("p");
    text = document.createElement("p");

    autor.textContent = "Autor: " + comment.autor;
    date.textContent = "Fecha: " + comment.date.getDay();
    text.textContent = comment.text;

    new_element.append(autor); 
    new_element.append(date); 
    new_element.append(text); 

    list.append(new_element);

}

comment_list.push(new Comment("Pepe", new Date(2025, 1, 23, 13, 44, 3), "Película muy interesante"));
postComment();
comment_list.push(new Comment("Pepa",new Date(2025, 1, 24, 10, 30), "Película poco interesante"));
postComment();

function validateComment(event)
{
    event.preventDefault();
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
        
        
        comment_list.push(new Comment(name+" "+email,new Date(), content));

        form.reset();
    }
    catch(error){
        console.error("Error de validación: ", error.message);
        errorM.textContent = error.message;
    }
}

