// ignore: file_names
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:video_player/video_player.dart';
import 'package:path_provider/path_provider.dart';
import 'package:http_parser/http_parser.dart';

import '../services/api_service.dart';

class GrabarPage extends StatefulWidget {

    final int idDosis;

  const GrabarPage({
  Key? key,
  required this.idDosis,
}) : super(key: key);



  @override
  // ignore: library_private_types_in_public_api
  _GrabarPageState createState() => _GrabarPageState();
  
}

class _GrabarPageState extends State<GrabarPage> with SingleTickerProviderStateMixin {
  File? _videoFile;
  VideoPlayerController? _videoController;
  bool _isSliderChanging = false;
  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      duration: const Duration(milliseconds: 800),
      vsync: this,
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeInOut),
    );
    _animationController.forward();
  }

  Future<void> _grabarVideo() async {
  final picker = ImagePicker();
  final pickedFile = await picker.pickVideo(source: ImageSource.camera);

  if (pickedFile != null) {
    final tempVideo = File(pickedFile.path);
    final Directory appDir = await getApplicationDocumentsDirectory();
    final String localPath = '${appDir.path}/${DateTime.now().millisecondsSinceEpoch}.mp4';

    // Guardar el video físicamente
    final savedVideo = await tempVideo.copy(localPath);

    _videoFile = savedVideo;
    _videoController = VideoPlayerController.file(_videoFile!);

    await _videoController!.initialize();
    final duration = _videoController!.value.duration;

    if (duration.inSeconds > 10) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('⏱️ El video no debe durar más de 10 segundos')),
      );
      _videoFile = null;
      _videoController = null;
      setState(() {});
      return;
    }

    setState(() {});
  }
}

  Future<void> _cargarVideo() async {
    if (_videoFile == null || _videoController == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('No hay video para enviar')),
      );
      return;
    }

    final duration = _videoController!.value.duration;
    if (duration.inSeconds > 10) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('⏱️ El video supera los 10 segundos.')),
      );
      return;
    }

    try {
      await _apiService.uploadVideo(
        idDosis: widget.idDosis,
        videoFile: _videoFile!,
      );

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('✅ Video subido correctamente')),
      );
      Navigator.pop(context, true); 
    } catch (e) {
      print('Error al subir video: $e');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('❌ Error al subir el video')),
      );
    }
  }



  String _formatDuration(Duration duration) {
    final minutes = duration.inMinutes.toString().padLeft(2, '0');
    final seconds = (duration.inSeconds % 60).toString().padLeft(2, '0');
    return '$minutes:$seconds';
  }

  @override
  void dispose() {
    _videoController?.dispose();
    _animationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
  return Scaffold(
    backgroundColor: const Color(0xFFF9F8EC),
    appBar: AppBar(
      elevation: 0,
      backgroundColor: Colors.transparent, // transparente para permitir gradiente
      title: const Text(
        'Grabar y subir video',
        style: TextStyle(
          color: Colors.white,
          fontWeight: FontWeight.w600,
        ),
      ),
      centerTitle: true,
      iconTheme: const IconThemeData(color: Colors.white),
      flexibleSpace: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFF47A485),
              Color(0xFF2D3D5D).withOpacity(0.8),
            ],
          ),
        ),
      ),
    ),
    body: FadeTransition(
      opacity: _fadeAnimation,
      child: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
            colors: [
              const Color(0xFF4E6BA6).withOpacity(0.1),
              const Color(0xFFF9F8EC),
            ],
          ),
        ),
        child: SingleChildScrollView(
          child: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                _buildInstructionsCard(),
                const SizedBox(height: 30),
                _buildVideoPreview(),
                const SizedBox(height: 30),
                _buildActionButtons(),
              ], 
            ),
          ),
        ),
      ),
    ),
  );
}


  Widget _buildInstructionsCard() {
    return Card(
      elevation: 4,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            const Icon(
              Icons.tips_and_updates,
              size: 40,
              color: Color(0xFF4E6BA6),
            ),
            const SizedBox(height: 15),
            const Text(
              'Instrucciones',
              style: TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
                color: Color(0xFF4E6BA6),
              ),
            ),
            const SizedBox(height: 15),
            _buildInstructionStep(
              '1',
              'Presiona "Iniciar grabación" para comenzar',
              Icons.videocam,
            ),
            _buildInstructionStep(
              '2',
              'Mantén el dispositivo estable durante la grabación',
              Icons.stay_current_portrait,
            ),
            _buildInstructionStep(
              '3',
              'Revisa el video antes de enviarlo',
              Icons.check_circle,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInstructionStep(String number, String text, IconData icon) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        children: [
          Container(
            width: 30,
            height: 30,
            decoration: BoxDecoration(
              color: const Color(0xFF4E6BA6),
              borderRadius: BorderRadius.circular(15),
            ),
            child: Center(
              child: Text(
                number,
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
          ),
          const SizedBox(width: 15),
          Expanded(
            child: Text(
              text,
              style: const TextStyle(
                fontSize: 16,
                color: Color(0xFF666666),
              ),
            ),
          ),
          Icon(icon, color: const Color(0xFF4E6BA6)),
        ],
      ),
    );
  }

  Widget _buildVideoPreview() {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            spreadRadius: 2,
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(20),
        child: _videoFile == null
            ? Container(
                height: 250,
                width: double.infinity,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [
                      const Color(0xFF47A485),
                      const Color(0xFF47A485).withOpacity(0.8),
                    ],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: const [
                    Icon(
                      Icons.videocam,
                      size: 80,
                      color: Colors.white,
                    ),
                    SizedBox(height: 15),
                    Text(
                      'No hay video seleccionado',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              )
            : Column(
                children: [
                  AspectRatio(
                    aspectRatio: _videoController!.value.aspectRatio,
                    child: VideoPlayer(_videoController!),
                  ),
                  _buildVideoControls(),
                ],
              ),
      ),
    );
  }

  Widget _buildVideoControls() {
    if (_videoController == null) return const SizedBox.shrink();
    
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.only(
          bottomLeft: Radius.circular(20),
          bottomRight: Radius.circular(20),
        ),
      ),
      child: Column(
        children: [
          Row(
            children: [
              IconButton(
                icon: Icon(
                  _videoController!.value.isPlaying
                      ? Icons.pause
                      : Icons.play_arrow,
                  color: const Color(0xFF4E6BA6),
                  size: 28,
                ),
                onPressed: () {
                  setState(() {
                    _videoController!.value.isPlaying
                        ? _videoController!.pause()
                        : _videoController!.play();
                  });
                },
              ),
              Expanded(
                child: SliderTheme(
                  data: SliderTheme.of(context).copyWith(
                    activeTrackColor: const Color(0xFF4E6BA6),
                    inactiveTrackColor: const Color(0xFF4E6BA6).withOpacity(0.2),
                    thumbColor: const Color(0xFF4E6BA6),
                    overlayColor: const Color(0xFF4E6BA6).withOpacity(0.1),
                  ),
                  child: Slider(
                    value: _videoController!.value.position.inSeconds.toDouble(),
                    max: _videoController!.value.duration.inSeconds.toDouble(),
                    onChanged: (value) {
                      setState(() {
                        _isSliderChanging = true;
                        _videoController!.seekTo(Duration(seconds: value.toInt()));
                      });
                    },
                    onChangeEnd: (_) {
                      setState(() {
                        _isSliderChanging = false;
                      });
                    },
                  ),
                ),
              ),
              Text(
                '${_formatDuration(_videoController!.value.position)} / ${_formatDuration(_videoController!.value.duration)}',
                style: const TextStyle(
                  color: Color(0xFF666666),
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Column(
      children: [
        _buildActionButton(
          onPressed: _grabarVideo,
          icon: Icons.videocam,
          label: 'Iniciar grabación',
          color: const Color(0xFF2D3D5D),
        ),
        const SizedBox(height: 15),
        _buildActionButton(
          onPressed: _cargarVideo,
          icon: Icons.upload_file,
          label: 'Enviar video',
          color: const Color(0xFF4E6BA6),
        ),
      ],
    );
  }

  Widget _buildActionButton({
    required VoidCallback onPressed,
    required IconData icon,
    required String label,
    required Color color,
  }) {
    return Container(
      width: double.infinity,
      height: 56,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: color.withOpacity(0.3),
            blurRadius: 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: ElevatedButton.icon(
        onPressed: onPressed,
        icon: Icon(icon),
        label: Text(
          label,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: color,
          foregroundColor: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
        ),
      ),
    );
  }
}
