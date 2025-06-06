import 'dart:convert';
import 'package:http/http.dart' as http;
import 'dart:io';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http_parser/http_parser.dart';


class ApiService {
  final String baseUrl = 'http://192.168.145.250:8000/api';
  final FlutterSecureStorage _storage = FlutterSecureStorage();
  static const String _tokenKey = 'auth_token';
  static const String _userIdKey = 'user_id'; // 👈 nuevo

  Future<bool> loginPaciente(String ci) async {
    print('Intentando login con CI: $ci');
    print('Enviando a: $baseUrl/login-paciente');
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login-paciente'),
        headers: {
          'Content-Type': 'application/json',
        },
        body: jsonEncode({'ci': ci}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final token = data['access_token'];
        print(token);
        final userId =
            data['user']['id']; // 🔥 capturamos el ID del paciente logeado

        // Guardar el token de forma segura
        await _storage.write(key: _tokenKey, value: token);

        // Guardar el id del usuario
        await _storage.write(key: _userIdKey, value: userId.toString());

        return true;
      } else {
        print('Error en login: ${response.body}');
        return false;
      }
    } catch (e) {
      print('Excepción en loginPaciente: $e');
      return false;
    }
  }

  // Obtener lista de usuarios usando el token almacenado
  Future<List<dynamic>> getDosisPaciente() async {
    final token = await _storage.read(key: _tokenKey);

    if (token == null) {
      throw Exception('No hay sesión activa. Inicia sesión primero.');
    }

    final response = await http.get(
      Uri.parse('$baseUrl/dosis-movil'), // 🔥 cambiás al endpoint correcto
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Error al cargar dosis: ${response.statusCode}');
    }
  }
  
   Future<void> uploadVideo({
      required int idDosis,
      required File videoFile,
    }) async {
      final token = await _storage.read(key: _tokenKey); // tu sistema de almacenamiento seguro

      if (token == null) {
        throw Exception('No hay sesión activa. Inicia sesión primero.');
      }

    final request = http.MultipartRequest(
        'POST',
        Uri.parse('$baseUrl/edit-video/$idDosis'),
      );
      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      // 👇 El nombre 'video' debe coincidir con Laravel
      request.files.add(await http.MultipartFile.fromPath(
        'video',
        videoFile.path,
        contentType: MediaType('video', 'mp4'),
      ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        print('✅ Subido correctamente');
      } else {
        print('❌ Falló: ${response.body}');
      }
    }

Future<Map<String, dynamic>> getPacientePerfil() async {
  final token = await _storage.read(key: _tokenKey);

  if (token == null) {
    throw Exception('No hay sesión activa. Inicia sesión primero.');
  }

  final response = await http.get(
    Uri.parse('$baseUrl/paciente-perfil'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );

  if (response.statusCode == 200) {
    return jsonDecode(response.body);
  } else {
    throw Exception('Error al cargar perfil del paciente');
  }
}


  // Opcional: método para cerrar sesión
  Future<void> logoutPaciente() async {
    final token = await _storage.read(key: _tokenKey);

    if (token != null) {
      final response = await http.post(
        Uri.parse('$baseUrl/logout-paciente'),
        headers: {
          'Authorization': 'Bearer $token',
          'Accept': 'application/json',
        },
      );

      if (response.statusCode == 200) {
        print('Sesión cerrada correctamente desde backend');
      } else {
        print('Error al cerrar sesión desde backend: ${response.body}');
      }
    }

    // Borra el token localmente igual
    await _storage.delete(key: _tokenKey);
  }
   Future<List<Map<String, dynamic>>> getNotificacionesPendientes() async {
    final token = await _storage.read(key: _tokenKey);

    if (token == null) {
      throw Exception('No hay sesión activa.');
    }

    final response = await http.get(
      Uri.parse('$baseUrl/notificaciones'),
      headers: {
        'Authorization': 'Bearer $token',
        'Accept': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final List<dynamic> data = jsonDecode(response.body);
      print('Notificaciones: $data');
      return data.map((n) => Map<String, dynamic>.from(n)).toList();
    } else {
      throw Exception('Error al cargar notificaciones: ${response.body}');
    }
  }

  Future<void> marcarNotificacionesLeidas() async {
  final token = await _storage.read(key: _tokenKey);

  if (token == null) {
    throw Exception('No hay sesión activa.');
  }

  final response = await http.post(
    Uri.parse('$baseUrl/notificaciones/marcar-leidas'),
    headers: {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    },
  );

  if (response.statusCode != 200) {
    throw Exception('Error al marcar notificaciones como leídas');
  }
}

}
