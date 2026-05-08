import '/auth/firebase_auth/auth_util.dart';
import '/backend/backend.dart';
import '/flutter_flow/flutter_flow_theme.dart';
import '/flutter_flow/flutter_flow_util.dart';
import '/flutter_flow/flutter_flow_widgets.dart';
import 'dart:ui';
import '/index.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'meet_model.dart';
export 'meet_model.dart';

class MeetWidget extends StatefulWidget {
  const MeetWidget({super.key});

  static String routeName = 'meet';
  static String routePath = '/meet';

  @override
  State<MeetWidget> createState() => _MeetWidgetState();
}

class _MeetWidgetState extends State<MeetWidget> with TickerProviderStateMixin {
  late MeetModel _model;
  final scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  void initState() {
    super.initState();
    _model = createModel(context, () => MeetModel());
    _model.tabBarController = TabController(
      vsync: this,
      length: 3,
      initialIndex: 0,
    )..addListener(() => safeSetState(() {}));
  }

  @override
  void dispose() {
    _model.dispose();
    super.dispose();
  }

  // ─── dialogue détails d'un meet ─────────────────────────────────────────
  Future<void> _showMeetDetails(MeetRecord meet) async {
    await showDialog(
      context: context,
      builder: (context) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(children: [
                const Icon(Icons.video_camera_front_rounded,
                    color: Color(0xFF1156A1), size: 28),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    meet.titre.isNotEmpty ? meet.titre : 'Meet sans titre',
                    style: FlutterFlowTheme.of(context).titleMedium.override(
                          font: GoogleFonts.interTight(
                              fontWeight: FontWeight.w700),
                          color: const Color(0xFF1156A1),
                          letterSpacing: 0,
                        ),
                  ),
                ),
              ]),
              const Divider(height: 24),
              _detailRow(Icons.description_outlined, 'Description',
                  meet.description.isNotEmpty ? meet.description : 'Non renseignée'),
              const SizedBox(height: 12),
              _detailRow(Icons.link_rounded, 'Lien',
                  meet.lienMeet.isNotEmpty ? meet.lienMeet : 'Non renseigné'),
              const SizedBox(height: 12),
              _detailRow(
                  Icons.calendar_today_rounded,
                  'Début',
                  meet.dateDebut != null
                      ? dateTimeFormat('d MMM yyyy', meet.dateDebut)
                      : 'Non renseigné'),
              const SizedBox(height: 12),
              _detailRow(
                  Icons.event_available_rounded,
                  'Fin',
                  meet.dateFin != null
                      ? dateTimeFormat('d MMM yyyy', meet.dateFin)
                      : 'Non renseignée'),
              const SizedBox(height: 20),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () => Navigator.pop(context),
                  child: const Text('Fermer',
                      style: TextStyle(color: Color(0xFF1156A1))),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _detailRow(IconData icon, String label, String value) {
    return Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Icon(icon, size: 18, color: const Color(0xFF39D2C0)),
      const SizedBox(width: 8),
      Expanded(
        child: RichText(
          text: TextSpan(children: [
            TextSpan(
                text: '$label : ',
                style: TextStyle(
                    fontWeight: FontWeight.w600,
                    color: FlutterFlowTheme.of(context).primaryText,
                    fontSize: 13)),
            TextSpan(
                text: value,
                style: TextStyle(
                    color: FlutterFlowTheme.of(context).secondaryText,
                    fontSize: 13)),
          ]),
        ),
      ),
    ]);
  }

  // ─── dialogue modifier un meet ───────────────────────────────────────────
  Future<void> _showModifyDialog(MeetRecord meet) async {
    final titreCtrl = TextEditingController(text: meet.titre);
    final descCtrl = TextEditingController(text: meet.description);
    final lienCtrl = TextEditingController(text: meet.lienMeet);
    DateTime? dateDebut = meet.dateDebut;
    DateTime? dateFin = meet.dateFin;

    await showDialog(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setStateDialog) => Dialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(24),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Modifier le meet',
                      style: FlutterFlowTheme.of(context).titleMedium.override(
                            font: GoogleFonts.interTight(
                                fontWeight: FontWeight.w700),
                            color: const Color(0xFF1156A1),
                            letterSpacing: 0,
                          )),
                  const SizedBox(height: 16),
                  // Titre
                  TextField(
                    controller: titreCtrl,
                    decoration: InputDecoration(
                      labelText: 'Titre',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      focusedBorder: OutlineInputBorder(
                          borderSide: const BorderSide(
                              color: Color(0xFF1156A1), width: 2),
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Description
                  TextField(
                    controller: descCtrl,
                    maxLines: 2,
                    decoration: InputDecoration(
                      labelText: 'Description',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      focusedBorder: OutlineInputBorder(
                          borderSide: const BorderSide(
                              color: Color(0xFF1156A1), width: 2),
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Lien
                  TextField(
                    controller: lienCtrl,
                    decoration: InputDecoration(
                      labelText: 'Lien meet',
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12)),
                      focusedBorder: OutlineInputBorder(
                          borderSide: const BorderSide(
                              color: Color(0xFF1156A1), width: 2),
                          borderRadius: BorderRadius.circular(12)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  // Date début
                  _dialogDateRow(
                    label: 'Début',
                    date: dateDebut,
                    onTap: () async {
                      final p = await showDatePicker(
                        context: context,
                        initialDate: dateDebut ?? getCurrentTimestamp,
                        firstDate: DateTime(2020),
                        lastDate: DateTime(2050),
                      );
                      if (p != null)
                        setStateDialog(() {
                          dateDebut = DateTime(p.year, p.month, p.day);
                        });
                    },
                  ),
                  const SizedBox(height: 8),
                  // Date fin
                  _dialogDateRow(
                    label: 'Fin',
                    date: dateFin,
                    onTap: () async {
                      final p = await showDatePicker(
                        context: context,
                        initialDate:
                            dateFin ?? dateDebut ?? getCurrentTimestamp,
                        firstDate: dateDebut ?? DateTime(2020),
                        lastDate: DateTime(2050),
                      );
                      if (p != null)
                        setStateDialog(() {
                          dateFin = DateTime(p.year, p.month, p.day);
                        });
                    },
                  ),
                  const SizedBox(height: 20),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      TextButton(
                        onPressed: () => Navigator.pop(context),
                        child: const Text('Annuler',
                            style: TextStyle(color: Colors.grey)),
                      ),
                      const SizedBox(width: 8),
                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          backgroundColor: const Color(0xFF1156A1),
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        onPressed: () async {
                          await meet.reference.update(createMeetRecordData(
                            titre: titreCtrl.text,
                            description: descCtrl.text,
                            lienMeet: lienCtrl.text,
                            dateDebut: dateDebut,
                            dateFin: dateFin,
                          ));
                          if (context.mounted) Navigator.pop(context);
                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                            content: const Text('Meet modifié avec succès !'),
                            backgroundColor: const Color(0xFF43A047),
                            behavior: SnackBarBehavior.floating,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                          ));
                        },
                        child: const Text('Enregistrer',
                            style: TextStyle(color: Colors.white)),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _dialogDateRow(
      {required String label,
      required DateTime? date,
      required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: date != null
              ? const Color(0xFFE8F5E9)
              : const Color(0xFFF5F5F5),
          borderRadius: BorderRadius.circular(10),
          border: Border.all(
              color: date != null
                  ? const Color(0xFF43A047)
                  : Colors.grey.shade300),
        ),
        child: Row(children: [
          Icon(Icons.calendar_today_rounded,
              size: 16,
              color: date != null
                  ? const Color(0xFF43A047)
                  : Colors.grey),
          const SizedBox(width: 8),
          Text(
            '$label : ${date != null ? dateTimeFormat("d MMM yyyy", date) : "Non défini"}',
            style: TextStyle(
                fontSize: 13,
                color: date != null
                    ? const Color(0xFF2E7D32)
                    : Colors.grey),
          ),
        ]),
      ),
    );
  }

  // ─── card d'un meet ──────────────────────────────────────────────────────
  Widget _buildMeetCard(MeetRecord meet) {
    final now = DateTime.now();
    final isActive = meet.dateDebut != null &&
        meet.dateFin != null &&
        now.isAfter(meet.dateDebut!) &&
        now.isBefore(meet.dateFin!);
    final isUpcoming =
        meet.dateDebut != null && meet.dateDebut!.isAfter(now);

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.07),
            blurRadius: 12,
            offset: const Offset(0, 4),
          )
        ],
        border: Border.all(
          color: isActive
              ? const Color(0xFF43A047)
              : isUpcoming
                  ? const Color(0xFF1156A1).withOpacity(0.3)
                  : Colors.grey.shade200,
          width: 1.2,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── ligne titre + badge ──────────────────────────
            Row(
              children: [
                Expanded(
                  child: Text(
                    meet.titre.isNotEmpty ? meet.titre : 'Sans titre',
                    style: FlutterFlowTheme.of(context).bodyMedium.override(
                          font: GoogleFonts.inter(fontWeight: FontWeight.w700),
                          color: const Color(0xFF1A237E),
                          fontSize: 16,
                          letterSpacing: 0,
                        ),
                  ),
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: isActive
                        ? const Color(0xFFE8F5E9)
                        : isUpcoming
                            ? const Color(0xFFE3F2FD)
                            : const Color(0xFFF5F5F5),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                        color: isActive
                            ? const Color(0xFF43A047)
                            : isUpcoming
                                ? const Color(0xFF1156A1)
                                : Colors.grey),
                  ),
                  child: Text(
                    isActive
                        ? 'Actif'
                        : isUpcoming
                            ? 'À venir'
                            : 'Passé',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: isActive
                          ? const Color(0xFF2E7D32)
                          : isUpcoming
                              ? const Color(0xFF1156A1)
                              : Colors.grey,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            // ── dates ────────────────────────────────────────
            Row(children: [
              const Icon(Icons.calendar_today_rounded,
                  size: 16, color: Color(0xFF39D2C0)),
              const SizedBox(width: 6),
              Text(
                meet.dateDebut != null
                    ? dateTimeFormat('d MMM yyyy', meet.dateDebut)
                    : 'Date non définie',
                style: FlutterFlowTheme.of(context).bodySmall.override(
                      font: GoogleFonts.inter(fontWeight: FontWeight.w500),
                      fontSize: 13,
                      letterSpacing: 0,
                    ),
              ),
              if (meet.dateFin != null) ...[
                const Text(' → ',
                    style: TextStyle(color: Colors.grey, fontSize: 13)),
                Text(
                  dateTimeFormat('d MMM yyyy', meet.dateFin),
                  style: FlutterFlowTheme.of(context).bodySmall.override(
                        font: GoogleFonts.inter(fontWeight: FontWeight.w500),
                        fontSize: 13,
                        letterSpacing: 0,
                      ),
                ),
              ],
            ]),
            const SizedBox(height: 14),
            // ── boutons actions ──────────────────────────────
            Row(
              children: [
                // Voir
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _showMeetDetails(meet),
                    icon: const Icon(Icons.visibility_outlined,
                        size: 16, color: Color(0xFF39D2C0)),
                    label: const Text('Voir',
                        style: TextStyle(
                            color: Color(0xFF39D2C0), fontSize: 13)),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFF39D2C0)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                // Modifier
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () => _showModifyDialog(meet),
                    icon: const Icon(Icons.edit_outlined,
                        size: 16, color: Color(0xFF574FB5)),
                    label: const Text('Modifier',
                        style: TextStyle(
                            color: Color(0xFF574FB5), fontSize: 13)),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFF574FB5)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                // Supprimer
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: () async {
                      final confirm = await showDialog<bool>(
                        context: context,
                        builder: (context) => AlertDialog(
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16)),
                          title: const Text('Supprimer ce meet ?'),
                          content: const Text(
                              'Cette action est irréversible.'),
                          actions: [
                            TextButton(
                              onPressed: () => Navigator.pop(context, false),
                              child: const Text('Annuler'),
                            ),
                            ElevatedButton(
                              style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.red),
                              onPressed: () => Navigator.pop(context, true),
                              child: const Text('Supprimer',
                                  style: TextStyle(color: Colors.white)),
                            ),
                          ],
                        ),
                      );
                      if (confirm == true) {
                        await meet.reference.delete();
                        ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                          content: const Text('Meet supprimé.'),
                          backgroundColor: Colors.red.shade700,
                          behavior: SnackBarBehavior.floating,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ));
                      }
                    },
                    icon: const Icon(Icons.delete_outline,
                        size: 16, color: Color(0xFFE53935)),
                    label: const Text('Supprimer',
                        style: TextStyle(
                            color: Color(0xFFE53935), fontSize: 13)),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(color: Color(0xFFE53935)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(8)),
                      padding: const EdgeInsets.symmetric(vertical: 8),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  // ─── liste filtrée ───────────────────────────────────────────────────────
  Widget _buildMeetList({
    required Query<Map<String, dynamic>> Function(
            Query<Map<String, dynamic>> q)
        queryBuilder,
    required String emptyText,
  }) {
    return StreamBuilder<List<MeetRecord>>(
      stream: queryMeetRecord(
        queryBuilder: (q) => queryBuilder(q),
      ),
      builder: (context, snapshot) {
        if (!snapshot.hasData) {
          return Center(
            child: CircularProgressIndicator(
              color: FlutterFlowTheme.of(context).primary,
            ),
          );
        }
        final meets = snapshot.data!;
        if (meets.isEmpty) {
          return Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.event_busy_rounded,
                    size: 64, color: Colors.grey.shade300),
                const SizedBox(height: 16),
                Text(emptyText,
                    style: FlutterFlowTheme.of(context).bodyMedium.override(
                          font: GoogleFonts.inter(),
                          color: FlutterFlowTheme.of(context).secondaryText,
                          letterSpacing: 0,
                        )),
              ],
            ),
          );
        }
        return ListView.builder(
          padding: const EdgeInsets.only(top: 8, bottom: 16),
          itemCount: meets.length,
          itemBuilder: (context, index) => _buildMeetCard(meets[index]),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final now = getCurrentTimestamp;

    return GestureDetector(
      onTap: () {
        FocusScope.of(context).unfocus();
        FocusManager.instance.primaryFocus?.unfocus();
      },
      child: Scaffold(
        key: scaffoldKey,
        backgroundColor: FlutterFlowTheme.of(context).secondaryBackground,
        body: Column(
          children: [
            // ── header ──────────────────────────────────────────────
            Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Color(0xFF1156A1), Color(0xFF1976D2)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
              ),
              child: SafeArea(
                bottom: false,
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
                  child: Row(
                    children: [
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Bonjour !',
                                style: FlutterFlowTheme.of(context)
                                    .bodySmall
                                    .override(
                                      font: GoogleFonts.inter(),
                                      color: Colors.white60,
                                      letterSpacing: 0,
                                    )),
                            const SizedBox(height: 2),
                            Text('Mes réunions',
                                style: FlutterFlowTheme.of(context)
                                    .headlineSmall
                                    .override(
                                      font: GoogleFonts.interTight(
                                          fontWeight: FontWeight.w700),
                                      color: Colors.white,
                                      letterSpacing: 0,
                                    )),
                          ],
                        ),
                      ),
                      // bouton ajouter
                      ElevatedButton.icon(
                        onPressed: () =>
                            context.pushNamed(AjoutermeetWidget.routeName),
                        icon: const Icon(Icons.add, size: 18),
                        label: const Text('Ajouter'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.white,
                          foregroundColor: const Color(0xFF1156A1),
                          elevation: 0,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(
                              horizontal: 14, vertical: 8),
                          textStyle: const TextStyle(
                              fontWeight: FontWeight.w600, fontSize: 14),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // ── TabBar ───────────────────────────────────────────────
            Container(
              color: Colors.white,
              child: TabBar(
                labelColor: const Color(0xFF1156A1),
                unselectedLabelColor: Colors.grey,
                indicatorColor: const Color(0xFF1156A1),
                indicatorWeight: 3,
                labelStyle: GoogleFonts.interTight(
                    fontWeight: FontWeight.w600, fontSize: 14),
                unselectedLabelStyle:
                    GoogleFonts.interTight(fontWeight: FontWeight.normal),
                tabs: const [
                  Tab(text: 'À venir'),
                  Tab(text: 'Passés'),
                  Tab(text: 'Tous'),
                ],
                controller: _model.tabBarController,
                onTap: (i) async {
                  [() async {}, () async {}, () async {}][i]();
                },
              ),
            ),

            // ── TabBarView ───────────────────────────────────────────
            Expanded(
              child: TabBarView(
                controller: _model.tabBarController,
                children: [
                  // À venir
                  _buildMeetList(
                    queryBuilder: (q) => q
                        .where('date_debut',
                            isGreaterThanOrEqualTo: now)
                        .orderBy('date_debut', descending: false),
                    emptyText: 'Aucune réunion à venir.',
                  ),
                  // Passés
                  _buildMeetList(
                    queryBuilder: (q) => q
                        .where('date_fin', isLessThan: now)
                        .orderBy('date_fin', descending: true),
                    emptyText: 'Aucune réunion passée.',
                  ),
                  // Tous
                  _buildMeetList(
                    queryBuilder: (q) =>
                        q.orderBy('date_debut', descending: false),
                    emptyText: 'Aucune réunion.',
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
