import 'package:flutter/material.dart';
import 'package:video_player/video_player.dart';
import 'historial.dart';

class TutorialPage extends StatefulWidget {
  const TutorialPage({super.key});

  @override
  VideoDemoState createState() => VideoDemoState();
}

class VideoDemoState extends State<TutorialPage> with SingleTickerProviderStateMixin {
  late VideoPlayerController _controller;
  late Future<void> _initializeVideoPlayerFuture;
  late AnimationController _animationController;
  bool _isPlaying = false;
  bool _isDragging = false;

  @override
  void initState() {
    super.initState();
    _controller = VideoPlayerController.asset("lib/video/Tutorial.mp4");
    _initializeVideoPlayerFuture = _controller.initialize();
    _controller.setLooping(true);
    _controller.setVolume(1.0);

    _animationController = AnimationController(
      duration: const Duration(milliseconds: 300),
      vsync: this,
    );

    _controller.addListener(() {
      if (_controller.value.position == _controller.value.duration) {
        _showVideoEndModal();
      }
      if (!_isDragging) {
        setState(() {});
      }
    });
  }

  @override
  void dispose() {
    _controller.dispose();
    _animationController.dispose();
    super.dispose();
  }

  String _formatDuration(Duration duration) {
    String twoDigits(int n) => n.toString().padLeft(2, '0');
    final minutes = twoDigits(duration.inMinutes.remainder(60));
    final seconds = twoDigits(duration.inSeconds.remainder(60));
    return "$minutes:$seconds";
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F6FA),
      appBar: AppBar(
        elevation: 0,
        backgroundColor: Colors.transparent, // ← transparente para que se vea el gradiente
        centerTitle: true,
        title: const Text(
          "Tutorial",
          style: TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            letterSpacing: 0.5,
            color: Colors.white, // ← color del texto
          ),
        ),
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(30)),
        ),
        flexibleSpace: Container(
          decoration: BoxDecoration(
            borderRadius: const BorderRadius.vertical(bottom: Radius.circular(30)),
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
      body: FutureBuilder(
        future: _initializeVideoPlayerFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.done) {
            return Column(
              children: [
                Expanded(
                  child: Container(
                    margin: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(20),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.2),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
                        ),
                      ],
                    ),
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(20),
                      child: Stack(
                        alignment: Alignment.center,
                        children: [
                          AspectRatio(
                            aspectRatio: _controller.value.aspectRatio,
                            child: VideoPlayer(_controller),
                          ),
                          if (!_isPlaying)
                            Container(
                              decoration: BoxDecoration(
                                color: Colors.black26,
                                shape: BoxShape.circle,
                                border: Border.all(color: Colors.white, width: 2),
                              ),
                              child: IconButton(
                                iconSize: 50,
                                icon: const Icon(Icons.play_arrow, color: Colors.white),
                                onPressed: () {
                                  setState(() {
                                    _isPlaying = true;
                                    _controller.play();
                                    _animationController.forward();
                                  });
                                },
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                ),
                _buildVideoControls(),
                _buildActionButtons(),
              ],
            );
          } else {
            return const Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(Color(0xFF4E81C4)),
              ),
            );
          }
        },
      ),
    );
  }

  Widget _buildVideoControls() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20),
      child: Column(
        children: [
          Row(
            children: [
              Text(
                _formatDuration(_controller.value.position),
                style: const TextStyle(color: Color(0xFF2D3D5D)),
              ),
              Expanded(
                child: SliderTheme(
                  data: SliderTheme.of(context).copyWith(
                    activeTrackColor: const Color(0xFF2D3D5D),
                    inactiveTrackColor: const Color(0xFF2D3D5D).withOpacity(0.2),
                    thumbColor: const Color(0xFF2D3D5D),
                    trackHeight: 4.0,
                    thumbShape: const RoundSliderThumbShape(enabledThumbRadius: 6),
                    overlayColor: const Color(0xFF2D3D5D).withOpacity(0.1),
                  ),
                  child: Slider(
                    value: _controller.value.position.inMilliseconds.toDouble(),
                    max: _controller.value.duration.inMilliseconds.toDouble(),
                    onChangeStart: (_) => _isDragging = true,
                    onChanged: (value) {
                      setState(() {
                        _controller.seekTo(Duration(milliseconds: value.toInt()));
                      });
                    },
                    onChangeEnd: (_) => _isDragging = false,
                  ),
                ),
              ),
              Text(
                _formatDuration(_controller.value.duration),
                style: const TextStyle(color: Color(0xFF2D3D5D)),
              ),
            ],
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceEvenly,
            children: [
              IconButton(
                icon: Icon(
                  Icons.replay_10,
                  color: const Color(0xFF2D3D5D),
                  size: 28,
                ),
                onPressed: () {
                  final newPosition = _controller.value.position - const Duration(seconds: 10);
                  _controller.seekTo(newPosition);
                },
              ),
              Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF2D3D5D),
                  shape: BoxShape.circle,
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF2D3D5D).withOpacity(0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: IconButton(
                  icon: Icon(
                    _isPlaying ? Icons.pause : Icons.play_arrow,
                    color: Colors.white,
                    size: 32,
                  ),
                  onPressed: () {
                    setState(() {
                      _isPlaying = !_isPlaying;
                      _isPlaying ? _controller.play() : _controller.pause();
                      _isPlaying
                          ? _animationController.forward()
                          : _animationController.reverse();
                    });
                  },
                ),
              ),
              IconButton(
                icon: Icon(
                  Icons.forward_10,
                  color: const Color(0xFF2D3D5D),
                  size: 28,
                ),
                onPressed: () {
                  final newPosition = _controller.value.position + const Duration(seconds: 10);
                  _controller.seekTo(newPosition);
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButtons() {
    return Padding(
      padding: const EdgeInsets.all(20),
      child: Row(
        children: [
          Expanded(
            child: _buildButton(
              onPressed: () => Navigator.pop(context),
              icon: Icons.arrow_back,
              label: 'Volver',
              color: const Color(0xFF2D3D5D),
            ),
          ),
          const SizedBox(width: 20),
          Expanded(
            child: _buildButton(
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) =>  Historial()),
                );
              },
              icon: Icons.videocam,
              label: 'Grabar',
              color: const Color(0xFF47A485),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildButton({
    required VoidCallback onPressed,
    required IconData icon,
    required String label,
    required Color color,
  }) {
    return Container(
      height: 50,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(25),
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
          padding: const EdgeInsets.symmetric(horizontal: 20),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(25),
          ),
        ),
      ),
    );
  }

  void _showVideoEndModal() {
    showDialog(
      context: context,
      builder: (context) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          title: const Text(
            "¡Tutorial Completado!",
            style: TextStyle(
              color: Color(0xFF4E81C4),
              fontWeight: FontWeight.bold,
            ),
          ),
          content: const Text(
            "¿Te gustaría comenzar a grabar ahora?",
            style: TextStyle(fontSize: 16),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text(
                "Más tarde",
                style: TextStyle(color: Colors.grey),
              ),
            ),
            ElevatedButton(
              onPressed: () {
                Navigator.pop(context);
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (context) =>  Historial()),
                );
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFFD34B82),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(15),
                ),
              ),
              child: const Text("Comenzar a Grabar"),
            ),
          ],
        );
      },
    );
  }
}
