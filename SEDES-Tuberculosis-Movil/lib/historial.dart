import 'package:flutter/material.dart';
import 'menu_usuario.dart';
import '../services/api_service.dart';
import 'grabar.dart';
import 'notificaciones.dart';

class Historial extends StatefulWidget {
  @override
  _HistorialState createState() => _HistorialState();
}

class _HistorialState extends State<Historial> {
  final ApiService _apiService = ApiService();
  List<Map<String, dynamic>> pendientes = [];
  List<Map<String, dynamic>> retrasos = [];
  List<Map<String, dynamic>> currentList = [];
  String selectedType = "pendientes";
  bool hayNotificacionesNuevas = false;
  List<Map<String, dynamic>> notificaciones = [];
  bool cargando = true;
  
  @override
  void initState() {
    super.initState();
    fetchDosis();
    cargarNotificaciones();
  }

  void fetchDosis() async {
    try {
      final List<dynamic> data = await _apiService.getDosisPaciente();
      setState(() {
        pendientes = data
            .where((d) => d['estado'].toString() == "1")
            .map((d) => Map<String, dynamic>.from(d))
            .toList();

        retrasos = data
            .where((d) => d['estado'].toString() == "3")
            .map((d) => Map<String, dynamic>.from(d))
            .toList();

        currentList = pendientes;
      });
    } catch (e) {
      print("Error al cargar dosis: $e");
    }
  }
Future<void> cargarNotificaciones() async {
  try {
    final data = await _apiService.getNotificacionesPendientes();
    final hayNuevas = data.any((n) => n['leido_en'] == null);

    setState(() {
      notificaciones = data;
      hayNotificacionesNuevas = hayNuevas;
      cargando = false;
    });

  } catch (e) {
    print('Error al cargar notificaciones: $e');
    setState(() => cargando = false);
  }
}
  void updateList(String type) {
    setState(() {
      selectedType = type;
      currentList = type == "pendientes" ? pendientes : retrasos;
    });
  }

  Widget buildButton(String label, IconData icon, Color color, String type) {
    final isSelected = selectedType == type;

    return GestureDetector(
      onTap: () => updateList(type),
      child: AnimatedContainer(
        duration: Duration(milliseconds: 300),
        padding: EdgeInsets.symmetric(horizontal: 16.0, vertical: 10.0),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(20.0),
          gradient: LinearGradient(
            colors: isSelected
                ? [color.withOpacity(0.9), color]
                : [Colors.white, Colors.white],
          ),
          boxShadow: [
            BoxShadow(
              color: isSelected
                  ? color.withOpacity(0.3)
                  : Colors.black.withOpacity(0.1),
              blurRadius: 8.0,
              offset: Offset(0, 3),
            )
          ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, color: isSelected ? Colors.white : color, size: 20),
            SizedBox(width: 8.0),
            Text(
              label,
              style: TextStyle(
                color: isSelected ? Colors.white : color,
                fontWeight: FontWeight.w600,
                fontSize: 14.0,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget buildCard(Map<String, dynamic> dosis) {
    final fechaHora = dosis['fechaGrabacion'].split(' ');
    final fecha = fechaHora[0];
    final horaCompleta = fechaHora[1];

    final partesHora = horaCompleta.split(':'); // separa en [HH, mm, ss]
    final horaMinuto = '${partesHora[0]}:${partesHora[1]}'; // solo hora:minuto

    final color = selectedType == "pendientes" ? Color(0xFF47A485) : Colors.red;
    final icon =
        selectedType == "pendientes" ? Icons.access_time : Icons.warning;

    return Container(
      margin: EdgeInsets.only(bottom: 16.0),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20.0),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 15,
            offset: Offset(0, 5),
          )
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(20.0),
            onTap: () async {
            if (dosis['estado'].toString() == "3") {
              // Si la dosis fue enviada fuera de tiempo, no permitimos acción
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('⛔ Esta dosis ya fue enviada fuera de fecha.')),
              );
              return;
            }

            final resultado = await Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => GrabarPage(idDosis: dosis['id']),
              ),
            );

            if (resultado == true) {
              fetchDosis(); // recarga dosis si el video fue subido
            }
          },
          child: Padding(
            padding: EdgeInsets.all(16.0),
            child: Row(
              children: [
                Container(
                  padding: EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: color, size: 24.0),
                ),
                SizedBox(width: 16.0),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            "Dosis ${dosis['nroDosis']}",
                            style: TextStyle(
                              fontWeight: FontWeight.w600,
                              fontSize: 16.0,
                              color: Color(0xFF5A7CBF),
                            ),
                          ),
                          Text(
                            horaMinuto,
                            style: TextStyle(
                                color: Colors.grey[600], fontSize: 14.0),
                          ),
                        ],
                      ),
                      SizedBox(height: 8.0),
                      Text(fecha,
                          style: TextStyle(
                              fontSize: 14.0, color: Colors.grey[600])),
                      SizedBox(height: 8.0),
                      Row(
                        children: [
                          Icon(Icons.location_on_outlined,
                              size: 16, color: Color(0xFF47A485)),
                          SizedBox(width: 4),
                          Text(
                            dosis['nombreEstablecimiento'] ??
                                'Sin establecimiento',
                            style: TextStyle(
                                fontSize: 14.0, color: Color(0xFF47A485)),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Color(0xFFF9F8EC),
     appBar: AppBar(
        elevation: 0,
        title: Text(
          "Mis dosis",
          style: TextStyle(
            color: Colors.white,
            fontSize: 24.0,
            fontWeight: FontWeight.w600,
          ),
        ),
        centerTitle: true,
        backgroundColor: Color(0xFF5A7CBF),
        iconTheme: IconThemeData(color: Colors.white),
        actions: [
        Builder(builder: (context) {
          return Stack(
            children: [
              IconButton(
                icon: Icon(Icons.notifications, color: Colors.white),
                onPressed: () async {
                    await showModalBottomSheet(
                      context: context,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                      ),
                      isScrollControlled: true,
                      builder: (context) => NotificacionesPanel(notificaciones: notificaciones),
                    );

                    // Al cerrar el panel, marcamos como leídas y recargamos
                    await _apiService.marcarNotificacionesLeidas();

                    final nuevas = await _apiService.getNotificacionesPendientes();
                    final hayNuevas = nuevas.any((n) => n['leido_en'] == null);

                    setState(() {
                      notificaciones = nuevas;
                      hayNotificacionesNuevas = hayNuevas;
                    });
                  },

              ),
              if (hayNotificacionesNuevas)
                Positioned(
                  right: 11,
                  top: 11,
                  child: Container(
                    width: 10,
                    height: 10,
                    decoration: BoxDecoration(
                      color: Colors.red,
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
            ],
          );
        }),
      ],

        flexibleSpace: Container(
          decoration: BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFF2D3D5D), Color(0xFF47A485)],
            ),
          ),
        ),
      ),
      drawer: MenuUsuario(),
      body: Column(
        children: [
          Container(
            padding: EdgeInsets.symmetric(vertical: 16.0),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(30),
                bottomRight: Radius.circular(30),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 10,
                  offset: Offset(0, 5),
                ),
              ],
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                buildButton("Pendientes", Icons.access_time, Color(0xFF47A485),
                    "pendientes"),
                buildButton("Enviados fuera de fecha", Icons.warning, Colors.red,
                    "retraso"),
              ],
            ),
          ),
          Expanded(
            child: currentList.isEmpty
                ? Center(child: Text("No se encontraron resultados"))
                : ListView.builder(
                    padding: EdgeInsets.all(16.0),
                    itemCount: currentList.length,
                    itemBuilder: (context, index) =>
                        buildCard(currentList[index]),
                  ),
          ),
        ],
      ),
    );
  }
}
