

// Tipos de datos en JavaScript

// 1. Number: Representa valores numéricos, tanto enteros como decimales.
let entero = 42;
let decimal = 3.14;

// 2. String: Representa una secuencia de caracteres, se puede definir con comillas simples o dobles.
let saludo = "Hola, mundo!";
let despedida = 'Adiós, mundo!';

// 3. Boolean: Representa un valor lógico que puede ser verdadero (true) o falso (false).
let esVerdadero = true;
let esFalso = false;

// 4. Null: Representa la ausencia intencional de cualquier valor.
let valorNulo = null;

// 5. Undefined: Representa una variable que ha sido declarada pero no inicializada.
let valorIndefinido;

// 6. Object: Representa una colección de propiedades y métodos.
let persona = {
    nombre: "Juan",
    edad: 30,
    saludar: function() {
        console.log("Hola, soy " + this.nombre);
    }
};

// Los objetos en JavaScript son estructuras que permiten almacenar múltiples valores en una sola entidad. 
// Estos valores se almacenan en forma de pares clave-valor. Las claves son cadenas de texto y los valores 
// pueden ser de cualquier tipo de dato, incluyendo otros objetos.

let coche = {
    marca: "Toyota",
    modelo: "Corolla",
    año: 2020,
    encender: function() {
        console.log("El coche está encendido");
    }
};

// 7. Array: Representa una lista ordenada de valores, que pueden ser de cualquier tipo de dato.
let numeros = [1, 2, 3, 4, 5];
let mezclado = [1, "dos", true, null];

// 8. Function: Representa un bloque de código reutilizable.
function sumar(a, b) {
    return a + b;
}

// Ejemplos de uso
console.log(entero); // 42
console.log(decimal); // 3.14
console.log(saludo); // "Hola, mundo!"
console.log(esVerdadero); // true
console.log(valorNulo); // null
console.log(valorIndefinido); // undefined
persona.saludar(); // "Hola, soy Juan"
console.log(numeros[2]); // 3
console.log(sumar(2, 3)); // 5

// Uso del objeto coche
console.log(coche.marca); // "Toyota"
coche.encender(); // "El coche está encendido"

// 9. Symbol: Representa un valor único y anónimo. Se utiliza principalmente para crear claves únicas para propiedades de objetos.
let simbolo1 = Symbol("descripcion");
let simbolo2 = Symbol("descripcion");
console.log(simbolo1 === simbolo2); // false

// 10. BigInt: Representa números enteros de gran tamaño. Se utiliza para trabajar con enteros que están fuera del rango de Number.
let numeroGrande = BigInt(123456789012345678901234567890);
let otroNumeroGrande = 123456789012345678901234567890n;
console.log(numeroGrande); // 123456789012345678901234567890n
console.log(otroNumeroGrande); // 123456789012345678901234567890n

// Uso de Symbol como clave de propiedad en un objeto
let objetoConSimbolo = {
    [simbolo1]: "valor asociado al símbolo"
};
console.log(objetoConSimbolo[simbolo1]); // "valor asociado al símbolo"

// Uso de BigInt en operaciones aritméticas
let sumaGrande = numeroGrande + otroNumeroGrande;
console.log(sumaGrande); // 246913578024691357802469135780n