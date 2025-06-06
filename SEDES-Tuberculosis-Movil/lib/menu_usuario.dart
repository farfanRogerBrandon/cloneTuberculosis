import 'package:flutter/material.dart';
import '../services/api_service.dart';
import 'tutorial.dart';
import 'historial.dart';
import 'main.dart';  // Importar LoginPage desde main.dart

class MenuUsuario extends StatefulWidget {
  const MenuUsuario({Key? key}) : super(key: key);

  @override
  _MenuUsuarioState createState() => _MenuUsuarioState();
}

class _MenuUsuarioState extends State<MenuUsuario> {
  String iniciales = "UE";
  String nombreCompleto = "Usuario Ejemplo";
  String ci = "12345678";

  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _cargarPerfil();
  }

  void _cargarPerfil() async {
    try {
      final perfil = await _apiService.getPacientePerfil();

      final nombre = perfil['nombres'] ?? '';
      final apellido = perfil['primerApellido'] ?? '';
      final cedula = perfil['ci'] ?? '';

      setState(() {
        iniciales = (nombre.isNotEmpty ? nombre[0] : '') +
            (apellido.isNotEmpty ? apellido[0] : '');
        nombreCompleto = "$nombre $apellido";
        ci = cedula;
      });
    } catch (e) {
      print('Error al cargar perfil: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              const Color(0xFF47A485),
              const Color(0xFF2D3D5D).withOpacity(0.8),
            ],
          ),
        ),
        child: Column(
          children: <Widget>[
            Container(
              padding: const EdgeInsets.only(top: 50, bottom: 20),
              child: Column(
                children: [
                  Container(
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      border: Border.all(color: Colors.white, width: 2),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.2),
                          blurRadius: 10,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: CircleAvatar(
                      radius: 50,
                      backgroundColor: Colors.white,
                      child: Text(
                        iniciales,
                        style: const TextStyle(
                          fontSize: 40.0,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF2D3D5D),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 15),
                  Text(
                    nombreCompleto,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22.0,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    ci,
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 16.0,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.only(
                    topLeft: Radius.circular(30),
                    topRight: Radius.circular(30),
                  ),
                ),
                child: ListView(
                  padding: const EdgeInsets.symmetric(vertical: 10),
                  children: <Widget>[
                    _buildMenuItem(
                      icon: Icons.history,
                      title: "Historial",
                      color: const Color(0xFFD94E8F),
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => Historial()),
                      ),
                    ),
                    _buildMenuItem(
                      icon: Icons.video_library,
                      title: "Tutorial",
                      color: const Color(0xFFADD9C9),
                      onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(builder: (context) => TutorialPage()),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            Container(
              color: Colors.white,
              child: _buildMenuItem(
                icon: Icons.exit_to_app,
                title: "Cerrar Sesión",
                color: Colors.red,
                onTap: () => _showLogoutDialog(context),
              ),
            ),
          ],
        ),
      ),
    );
  }


  Widget _buildMenuItem({
    required IconData icon,
    required String title,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      child: Card(
        elevation: 0,
        color: Colors.transparent,
        child: ListTile(
          onTap: onTap,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(15),
          ),
          leading: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color),
          ),
          title: Text(
            title,
            style: TextStyle(
              fontSize: 16.0,
              fontWeight: FontWeight.w500,
              color: color == Colors.red ? Colors.red : Colors.black87,
            ),
          ),
          trailing: Icon(
            Icons.arrow_forward_ios,
            size: 16,
            color: color.withOpacity(0.5),
          ),
        ),
      ),
    );
  }

void _showLogoutDialog(BuildContext context) {
  showDialog(
    context: context,
    builder: (BuildContext context) {
      return AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
        ),
        title: const Text(
          "Cerrar Sesión",
          style: TextStyle(fontWeight: FontWeight.bold),
        ),
        content: const Text(
          "¿Estás seguro de que quieres cerrar sesión?",
          style: TextStyle(fontSize: 16),
        ),
        actions: <Widget>[
          TextButton(
            child: const Text(
              "Cancelar",
              style: TextStyle(color: Colors.grey),
            ),
            onPressed: () => Navigator.of(context).pop(),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
            child: const Text("Cerrar Sesión"),
            onPressed: () async {
              Navigator.of(context).pop(); // Cierra el diálogo

              final apiService = ApiService();
              await apiService.logoutPaciente(); // Limpia el token

              // Redirige a Login y elimina historial de navegación
              Navigator.of(context).pushAndRemoveUntil(
                MaterialPageRoute(builder: (context) => const LoginPage()),
                (route) => false,
              );
            },
          ),
        ],
      );
    },
  );
}


}
