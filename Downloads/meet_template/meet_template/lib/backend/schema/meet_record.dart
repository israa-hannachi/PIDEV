import 'dart:async';

import 'package:collection/collection.dart';

import '/backend/schema/util/firestore_util.dart';
import '/backend/schema/util/schema_util.dart';

import 'index.dart';
import '/flutter_flow/flutter_flow_util.dart';

class MeetRecord extends FirestoreRecord {
  MeetRecord._(
    DocumentReference reference,
    Map<String, dynamic> data,
  ) : super(reference, data) {
    _initializeFields();
  }

  // "titre" field.
  String? _titre;
  String get titre => _titre ?? '';
  bool hasTitre() => _titre != null;

  // "description" field.
  String? _description;
  String get description => _description ?? '';
  bool hasDescription() => _description != null;

  // "lien_meet" field.
  String? _lienMeet;
  String get lienMeet => _lienMeet ?? '';
  bool hasLienMeet() => _lienMeet != null;

  // "create_at" field.
  DateTime? _createAt;
  DateTime? get createAt => _createAt;
  bool hasCreateAt() => _createAt != null;

  // "date_debut" field.
  DateTime? _dateDebut;
  DateTime? get dateDebut => _dateDebut;
  bool hasDateDebut() => _dateDebut != null;

  // "date_fin" field.
  DateTime? _dateFin;
  DateTime? get dateFin => _dateFin;
  bool hasDateFin() => _dateFin != null;

  void _initializeFields() {
    _titre = snapshotData['titre'] as String?;
    _description = snapshotData['description'] as String?;
    _lienMeet = snapshotData['lien_meet'] as String?;
    _createAt = snapshotData['create_at'] as DateTime?;
    _dateDebut = snapshotData['date_debut'] as DateTime?;
    _dateFin = snapshotData['date_fin'] as DateTime?;
  }

  static CollectionReference get collection =>
      FirebaseFirestore.instance.collection('meet');

  static Stream<MeetRecord> getDocument(DocumentReference ref) =>
      ref.snapshots().map((s) => MeetRecord.fromSnapshot(s));

  static Future<MeetRecord> getDocumentOnce(DocumentReference ref) =>
      ref.get().then((s) => MeetRecord.fromSnapshot(s));

  static MeetRecord fromSnapshot(DocumentSnapshot snapshot) => MeetRecord._(
        snapshot.reference,
        mapFromFirestore(snapshot.data() as Map<String, dynamic>),
      );

  static MeetRecord getDocumentFromData(
    Map<String, dynamic> data,
    DocumentReference reference,
  ) =>
      MeetRecord._(reference, mapFromFirestore(data));

  @override
  String toString() =>
      'MeetRecord(reference: ${reference.path}, data: $snapshotData)';

  @override
  int get hashCode => reference.path.hashCode;

  @override
  bool operator ==(other) =>
      other is MeetRecord &&
      reference.path.hashCode == other.reference.path.hashCode;
}

Map<String, dynamic> createMeetRecordData({
  String? titre,
  String? description,
  String? lienMeet,
  DateTime? createAt,
  DateTime? dateDebut,
  DateTime? dateFin,
}) {
  final firestoreData = mapToFirestore(
    <String, dynamic>{
      'titre': titre,
      'description': description,
      'lien_meet': lienMeet,
      'create_at': createAt,
      'date_debut': dateDebut,
      'date_fin': dateFin,
    }.withoutNulls,
  );

  return firestoreData;
}

class MeetRecordDocumentEquality implements Equality<MeetRecord> {
  const MeetRecordDocumentEquality();

  @override
  bool equals(MeetRecord? e1, MeetRecord? e2) {
    return e1?.titre == e2?.titre &&
        e1?.description == e2?.description &&
        e1?.lienMeet == e2?.lienMeet &&
        e1?.createAt == e2?.createAt &&
        e1?.dateDebut == e2?.dateDebut &&
        e1?.dateFin == e2?.dateFin;
  }

  @override
  int hash(MeetRecord? e) => const ListEquality().hash([
        e?.titre,
        e?.description,
        e?.lienMeet,
        e?.createAt,
        e?.dateDebut,
        e?.dateFin
      ]);

  @override
  bool isValidKey(Object? o) => o is MeetRecord;
}
