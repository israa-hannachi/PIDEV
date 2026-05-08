import '/auth/firebase_auth/auth_util.dart';
import '/backend/backend.dart';
import '/flutter_flow/flutter_flow_icon_button.dart';
import '/flutter_flow/flutter_flow_theme.dart';
import '/flutter_flow/flutter_flow_util.dart';
import '/flutter_flow/flutter_flow_widgets.dart';
import 'dart:ui';
import '/index.dart';
import 'package:cloud_firestore/cloud_firestore.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'ajoutermeet_model.dart';
export 'ajoutermeet_model.dart';

class AjoutermeetWidget extends StatefulWidget {
  const AjoutermeetWidget({super.key});

  static String routeName = 'ajoutermeet';
  static String routePath = '/ajoutermeet';

  @override
  State<AjoutermeetWidget> createState() => _AjoutermeetWidgetState();
}

class _AjoutermeetWidgetState extends State<AjoutermeetWidget> {
  late AjoutermeetModel _model;
  final scaffoldKey = GlobalKey<ScaffoldState>();

  @override
  void initState() {
    super.initState();
    _model = createModel(context, () => AjoutermeetModel());
    _model.textController1 ??= TextEditingController();
    _model.textFieldFocusNode1 ??= FocusNode();
    _model.textController2 ??= TextEditingController();
    _model.textFieldFocusNode2 ??= FocusNode();
    _model.textController3 ??= TextEditingController();
    _model.textFieldFocusNode3 ??= FocusNode();
    _model.textController4 ??= TextEditingController();
    _model.textFieldFocusNode4 ??= FocusNode();
  }

  @override
  void dispose() {
    _model.dispose();
    super.dispose();
  }

  Widget _buildTextField({
    required String label,
    required TextEditingController controller,
    required FocusNode focusNode,
    required String hint,
    String? Function(BuildContext, String?)? validator,
    int maxLines = 1,
  }) {
    return Padding(
      padding: const EdgeInsetsDirectional.fromSTEB(0, 16, 0, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsetsDirectional.fromSTEB(16, 0, 0, 6),
            child: Text(label,
                style: FlutterFlowTheme.of(context).bodyMedium.override(
                      font: GoogleFonts.inter(fontWeight: FontWeight.w600),
                      fontSize: 15,
                      letterSpacing: 0,
                    )),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: TextFormField(
              controller: controller,
              focusNode: focusNode,
              maxLines: maxLines,
              obscureText: false,
              decoration: InputDecoration(
                hintText: hint,
                hintStyle: FlutterFlowTheme.of(context).labelMedium.override(
                      font: GoogleFonts.inter(),
                      letterSpacing: 0,
                    ),
                filled: true,
                fillColor: const Color(0xFFF0FDFB),
                contentPadding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                enabledBorder: OutlineInputBorder(
                  borderSide:
                      const BorderSide(color: Color(0xFF39D2C0), width: 1.2),
                  borderRadius: BorderRadius.circular(12),
                ),
                focusedBorder: OutlineInputBorder(
                  borderSide:
                      const BorderSide(color: Color(0xFF1156A1), width: 2),
                  borderRadius: BorderRadius.circular(12),
                ),
                errorBorder: OutlineInputBorder(
                  borderSide: BorderSide(
                      color: FlutterFlowTheme.of(context).error, width: 1),
                  borderRadius: BorderRadius.circular(12),
                ),
                focusedErrorBorder: OutlineInputBorder(
                  borderSide: BorderSide(
                      color: FlutterFlowTheme.of(context).error, width: 2),
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              style: FlutterFlowTheme.of(context).bodyMedium.override(
                    font: GoogleFonts.inter(),
                    letterSpacing: 0,
                  ),
              validator: validator != null ? validator.asValidator(context) : null,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDatePicker({
    required String label,
    required DateTime? selectedDate,
    required VoidCallback onTap,
  }) {
    return Padding(
      padding: const EdgeInsetsDirectional.fromSTEB(16, 16, 16, 0),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label,
              style: FlutterFlowTheme.of(context).bodyMedium.override(
                    font: GoogleFonts.inter(fontWeight: FontWeight.w600),
                    fontSize: 15,
                    letterSpacing: 0,
                  )),
          const SizedBox(height: 8),
          InkWell(
            onTap: onTap,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: selectedDate != null
                    ? const Color(0xFFE8F5E9)
                    : const Color(0xFFF0FDFB),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: selectedDate != null
                      ? const Color(0xFF43A047)
                      : const Color(0xFF39D2C0),
                  width: 1.2,
                ),
              ),
              child: Row(
                children: [
                  Icon(Icons.calendar_today_rounded,
                      size: 20,
                      color: selectedDate != null
                          ? const Color(0xFF43A047)
                          : const Color(0xFF39D2C0)),
                  const SizedBox(width: 12),
                  Text(
                    selectedDate != null
                        ? dateTimeFormat('d MMM yyyy', selectedDate)
                        : 'Sélectionner une date',
                    style: FlutterFlowTheme.of(context).bodyMedium.override(
                          font: GoogleFonts.inter(
                              fontWeight: selectedDate != null
                                  ? FontWeight.w600
                                  : FontWeight.normal),
                          color: selectedDate != null
                              ? const Color(0xFF2E7D32)
                              : FlutterFlowTheme.of(context).secondaryText,
                          letterSpacing: 0,
                        ),
                  ),
                  const Spacer(),
                  if (selectedDate != null)
                    const Icon(Icons.check_circle,
                        color: Color(0xFF43A047), size: 18),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        FocusScope.of(context).unfocus();
        FocusManager.instance.primaryFocus?.unfocus();
      },
      child: Scaffold(
        key: scaffoldKey,
        backgroundColor: FlutterFlowTheme.of(context).primaryBackground,
        appBar: AppBar(
          backgroundColor: const Color(0xFF1156A1),
          automaticallyImplyLeading: false,
          leading: FlutterFlowIconButton(
            borderColor: Colors.transparent,
            borderRadius: 30,
            borderWidth: 1,
            buttonSize: 60,
            icon: const Icon(Icons.arrow_back_rounded,
                color: Colors.white, size: 28),
            onPressed: () async {
              context.pushNamed(MeetWidget.routeName);
            },
          ),
          title: Text(
            'Nouveau meet',
            style: FlutterFlowTheme.of(context).headlineMedium.override(
                  font: GoogleFonts.interTight(fontWeight: FontWeight.w600),
                  color: Colors.white,
                  fontSize: 22,
                  letterSpacing: 0,
                ),
          ),
          centerTitle: true,
          elevation: 0,
        ),
        body: SafeArea(
          top: true,
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.only(bottom: 32),
              child: Form(
                key: _model.formKey,
                autovalidateMode: AutovalidateMode.disabled,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // ── bannière ─────────────────────────────────────────
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Color(0xFF1156A1), Color(0xFF39D2C0)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.only(
                          bottomLeft: Radius.circular(24),
                          bottomRight: Radius.circular(24),
                        ),
                      ),
                      child: Text(
                        'Remplissez les informations de votre réunion',
                        style:
                            FlutterFlowTheme.of(context).bodyMedium.override(
                                  font: GoogleFonts.inter(),
                                  color: Colors.white70,
                                  fontSize: 14,
                                  letterSpacing: 0,
                                ),
                      ),
                    ),

                    // ── Titre ─────────────────────────────────────────────
                    _buildTextField(
                      label: 'Titre *',
                      controller: _model.textController1!,
                      focusNode: _model.textFieldFocusNode1!,
                      hint: 'Ex : Réunion de projet',
                      validator: _model.textController1Validator,
                    ),

                    // ── Description ───────────────────────────────────────
                    _buildTextField(
                      label: 'Description',
                      controller: _model.textController2!,
                      focusNode: _model.textFieldFocusNode2!,
                      hint: 'Décrivez l\'objectif de la réunion',
                      maxLines: 3,
                      validator: _model.textController2Validator,
                    ),

                    // ── Lien meet ─────────────────────────────────────────
                    _buildTextField(
                      label: 'Lien meet',
                      controller: _model.textController3!,
                      focusNode: _model.textFieldFocusNode3!,
                      hint: 'https://meet.google.com/...',
                    ),

                    // ── Participant ───────────────────────────────────────
                    _buildTextField(
                      label: 'Participant',
                      controller: _model.textController4!,
                      focusNode: _model.textFieldFocusNode4!,
                      hint: 'Nom du participant',
                    ),

                    // ── séparateur dates ──────────────────────────────────
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 24, 16, 0),
                      child: Row(children: [
                        Expanded(
                            child: Divider(
                                color: Colors.grey.shade300, thickness: 1)),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          child: Text('Dates',
                              style: FlutterFlowTheme.of(context)
                                  .bodySmall
                                  .override(
                                    font: GoogleFonts.inter(
                                        fontWeight: FontWeight.w600),
                                    color: FlutterFlowTheme.of(context)
                                        .secondaryText,
                                    letterSpacing: 0,
                                  )),
                        ),
                        Expanded(
                            child: Divider(
                                color: Colors.grey.shade300, thickness: 1)),
                      ]),
                    ),

                    // ── Date début ────────────────────────────────────────
                    _buildDatePicker(
                      label: 'Date de début *',
                      selectedDate: _model.dateDebutPicked,
                      onTap: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate:
                              _model.dateDebutPicked ?? getCurrentTimestamp,
                          firstDate: DateTime(2020),
                          lastDate: DateTime(2050),
                          builder: (context, child) {
                            return wrapInMaterialDatePickerTheme(
                              context, child!,
                              headerBackgroundColor:
                                  FlutterFlowTheme.of(context).primary,
                              headerForegroundColor:
                                  FlutterFlowTheme.of(context).info,
                              headerTextStyle: FlutterFlowTheme.of(context)
                                  .headlineLarge
                                  .override(
                                    font: GoogleFonts.interTight(
                                        fontWeight: FontWeight.w600),
                                    fontSize: 32,
                                    letterSpacing: 0,
                                  ),
                              pickerBackgroundColor:
                                  FlutterFlowTheme.of(context).secondaryBackground,
                              pickerForegroundColor:
                                  FlutterFlowTheme.of(context).primaryText,
                              selectedDateTimeBackgroundColor:
                                  FlutterFlowTheme.of(context).primary,
                              selectedDateTimeForegroundColor:
                                  FlutterFlowTheme.of(context).info,
                              actionButtonForegroundColor:
                                  FlutterFlowTheme.of(context).primaryText,
                              iconSize: 24,
                            );
                          },
                        );
                        if (picked != null) {
                          safeSetState(() {
                            _model.dateDebutPicked =
                                DateTime(picked.year, picked.month, picked.day);
                          });
                        }
                      },
                    ),

                    // ── Date fin ──────────────────────────────────────────
                    _buildDatePicker(
                      label: 'Date de fin *',
                      selectedDate: _model.dateFinPicked,
                      onTap: () async {
                        final picked = await showDatePicker(
                          context: context,
                          initialDate: _model.dateFinPicked ??
                              _model.dateDebutPicked ??
                              getCurrentTimestamp,
                          firstDate: _model.dateDebutPicked ?? DateTime(2020),
                          lastDate: DateTime(2050),
                          builder: (context, child) {
                            return wrapInMaterialDatePickerTheme(
                              context, child!,
                              headerBackgroundColor:
                                  FlutterFlowTheme.of(context).primary,
                              headerForegroundColor:
                                  FlutterFlowTheme.of(context).info,
                              headerTextStyle: FlutterFlowTheme.of(context)
                                  .headlineLarge
                                  .override(
                                    font: GoogleFonts.interTight(
                                        fontWeight: FontWeight.w600),
                                    fontSize: 32,
                                    letterSpacing: 0,
                                  ),
                              pickerBackgroundColor:
                                  FlutterFlowTheme.of(context).secondaryBackground,
                              pickerForegroundColor:
                                  FlutterFlowTheme.of(context).primaryText,
                              selectedDateTimeBackgroundColor:
                                  FlutterFlowTheme.of(context).primary,
                              selectedDateTimeForegroundColor:
                                  FlutterFlowTheme.of(context).info,
                              actionButtonForegroundColor:
                                  FlutterFlowTheme.of(context).primaryText,
                              iconSize: 24,
                            );
                          },
                        );
                        if (picked != null) {
                          safeSetState(() {
                            _model.dateFinPicked =
                                DateTime(picked.year, picked.month, picked.day);
                          });
                        }
                      },
                    ),

                    // ── Bouton Ajouter ────────────────────────────────────
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 36, 16, 0),
                      child: FFButtonWidget(
                        onPressed: () async {
                          // 1. Validation formulaire
                          if (_model.formKey.currentState == null ||
                              !_model.formKey.currentState!.validate()) {
                            return;
                          }
                          // 2. Validation dates obligatoires
                          if (_model.dateDebutPicked == null ||
                              _model.dateFinPicked == null) {
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: const Text(
                                  'Veuillez sélectionner les dates de début et de fin.'),
                              backgroundColor:
                                  FlutterFlowTheme.of(context).error,
                              behavior: SnackBarBehavior.floating,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12)),
                            ));
                            return;
                          }
                          // 3. Date fin >= date début
                          if (_model.dateFinPicked!
                              .isBefore(_model.dateDebutPicked!)) {
                            ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                              content: const Text(
                                  'La date de fin doit être après la date de début.'),
                              backgroundColor:
                                  FlutterFlowTheme.of(context).error,
                              behavior: SnackBarBehavior.floating,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12)),
                            ));
                            return;
                          }
                          // 4. CREATE Firestore avec toutes les valeurs
                          await MeetRecord.collection.doc().set(
                                createMeetRecordData(
                                  titre: _model.textController1!.text,
                                  description: _model.textController2!.text,
                                  lienMeet: _model.textController3!.text,
                                  createAt: getCurrentTimestamp,
                                  dateDebut: _model.dateDebutPicked,
                                  dateFin: _model.dateFinPicked,
                                ),
                              );
                          // 5. Confirmation + retour
                          ScaffoldMessenger.of(context).showSnackBar(SnackBar(
                            content: const Text('Meet ajouté avec succès !'),
                            backgroundColor: const Color(0xFF43A047),
                            behavior: SnackBarBehavior.floating,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                          ));
                          context.pushNamed(MeetWidget.routeName);
                        },
                        text: 'Ajouter le meet',
                        icon: const Icon(Icons.add_circle_outline,
                            size: 20, color: Colors.white),
                        options: FFButtonOptions(
                          width: double.infinity,
                          height: 52,
                          padding: const EdgeInsetsDirectional.fromSTEB(
                              16, 0, 16, 0),
                          iconPadding:
                              const EdgeInsetsDirectional.fromSTEB(0, 0, 0, 0),
                          color: const Color(0xFF1156A1),
                          textStyle: FlutterFlowTheme.of(context)
                              .titleSmall
                              .override(
                                font: GoogleFonts.interTight(
                                    fontWeight: FontWeight.w600),
                                color: Colors.white,
                                fontSize: 16,
                                letterSpacing: 0,
                              ),
                          elevation: 3,
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
