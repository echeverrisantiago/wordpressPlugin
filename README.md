# Instalación

En un sitio web en wordpress entrar al directorio de plugins y crear la carpeta `` weather-echeve `` y dentro de esta agregar todos los archivos del repositorio. 
En plugins dentro del panel de wordpress activar el plugin `` Weather Echeverri `` 

# Configuración

Al activar el plugin se creará el custom post type `` historical `` pero primero debemos configurar la aplicación en: Ajustes > Weather configuration 

En este panel agregamos la API, nuestra API Key y la city ID 

Ejemplo: 

API: http://api.openweathermap.org/data/2.5/weather 
API Key: 72e2ce66e9f90f35922f237bc79c0dcd
City id: 833

Al realizar terminar de diligenciar los datos se mostrará el botón para establecer conexión y una vez finalizado el proceso nuestro historical se actualizará con los datos
almacenados. 

# Listar datos

Automáticamente al activar el plugin se creará la página historical y esta listará todos los datos registrados previamente del webservice. 
