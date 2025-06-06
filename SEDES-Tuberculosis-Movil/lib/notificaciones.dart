import 'package:flutter/material.dart';


class NotificacionesPanel extends StatelessWidget {
  final List<Map<String, dynamic>> notificaciones;

  NotificacionesPanel({required this.notificaciones});

  @override
  Widget build(BuildContext context) {
    return DraggableScrollableSheet(
      expand: false,
      initialChildSize: 0.6,
      builder: (_, controller) => Container(
        padding: EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
        ),
        child: Column(
          children: [
            Text("NOTIFICACIONES", style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
            SizedBox(height: 12),
            if (notificaciones.isEmpty)
              Text("No hay notificaciones nuevas.")
            else
              Expanded(
                child: ListView.builder(
                  controller: controller,
                  itemCount: notificaciones.length,
                  itemBuilder: (context, index) {
                    final notif = notificaciones[index];
                    return Card(
                      color: Color(0xFFF5F5F5),
                      margin: EdgeInsets.symmetric(vertical: 8),
                      child: ListTile(
                        leading: Icon(Icons.notification_important, color: Colors.amber),
                        title: Text(notif['titulo']),
                        subtitle: Text(notif['mensaje']),
                        trailing: notif['leido_en'] == null
                            ? Container(
                                padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                                decoration: BoxDecoration(
                                  color: Colors.redAccent,
                                  borderRadius: BorderRadius.circular(8),
                                ),
                                child: Text("NUEVO", style: TextStyle(color: Colors.white, fontSize: 12)),
                              )
                            : null,
                      ),
                    );
                  },
                ),
              ),
          ],
        ),
      ),
    );
  }
}

