import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:sedes_tuberculosis/main.dart';

void main() {
  testWidgets('Login page loads correctly', (WidgetTester tester) async {
    // Construye el widget
    await tester.pumpWidget(const MyApp());

    // Verifica que el texto del título está presente
    expect(find.text('Área de Tuberculosis'), findsOneWidget);
    
    // Verifica que hay un campo de credencial
    expect(find.byType(TextField), findsNWidgets(2));

    // Verifica el botón de iniciar sesión
    expect(find.text('Iniciar Sesión'), findsOneWidget);
    
    // Simula un tap en el botón de inicio de sesión
    await tester.tap(find.text('Iniciar Sesión'));
    await tester.pump();

    // Opcional: Verifica que se navega a otra pantalla (Historial)
    expect(find.text('Historial de Pacientes'), findsNothing);
  });
}
