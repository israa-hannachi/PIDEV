import 'dart:async';

import 'package:collection/collection.dart';

import '/backend/schema/util/firestore_util.dart';
import '/backend/schema/util/schema_util.dart';

import 'index.dart';
import '/flutter_flow/flutter_flow_util.dart';

class ParticipantRecord extends FirestoreRecord {
  ParticipantRecord._(
    DocumentReference reference,
    Map<String, dynamic> data,
  ) : super(reference, data) {
    _initializeFields();
  }

  // "nom" field.
  String? _nom;
  String get nom => _nom ?? '';
  bool hasNom() => _nom != null;

  // "prenom" field.
  String? _prenom;
  String get prenom => _prenom ?? '';
  bool hasPrenom() => _prenom != null;

  // "email" field.
  String? _email;
  String get email => _email ?? '';
  bool hasEmail() => _email != null;

  // "role" field.
  String? _role;
  String get role => _role ?? '';
  bool hasRole() => _role != null;

  // "created_at" field.
  DateTime? _createdAt;
  DateTime? get createdAt => _createdAt;
  bool hasCreatedAt() => _createdAt != null;

  // "smtp_email" field.
  String? _smtpEmail;
  String get smtpEmail => _smtpEmail ?? '';
  bool hasSmtpEmail() => _smtpEmail != null;

  // "smtp_app_password" field.
  String? _smtpAppPassword;
  String get smtpAppPassword => _smtpAppPassword ?? '';
  bool hasSmtpAppPassword() => _smtpAppPassword != null;

  void _initializeFields() {
    _nom = snapshotData['nom'] as String?;
    _prenom = snapshotData['prenom'] as String?;
    _email = snapshotData['email'] as String?;
    _role = snapshotData['role'] as String?;
    _createdAt = snapshotData['created_at'] as DateTime?;
    _smtpEmail = snapshotData['smtp_email'] as String?;
    _smtpAppPassword = snapshotData['smtp_app_password'] as String?;
  }

  static CollectionReference get collection =>
      FirebaseFirestore.instance.collection('participant');

  static Stream<ParticipantRecord> getDocument(DocumentReference ref) =>
      ref.snapshots().map((s) => ParticipantRecord.fromSnapshot(s));

  static Future<ParticipantRecord> getDocumentOnce(DocumentReference ref) =>
      ref.get().then((s) => ParticipantRecord.fromSnapshot(s));

  static ParticipantRecord fromSnapshot(DocumentSnapshot snapshot) =>
      ParticipantRecord._(
        snapshot.reference,
        mapFromFirestore(snapshot.data() as Map<String, dynamic>),
      );

  static ParticipantRecord getDocumentFromData(
    Map<String, dynamic> data,
    DocumentReference reference,
  ) =>
      ParticipantRecord._(reference, mapFromFirestore(data));

  @override
  String toString() =>
      'ParticipantRecord(reference: ${reference.path}, data: $snapshotData)';

  @override
  int get hashCode => reference.path.hashCode;

  @override
  bool operator ==(other) =>
      other is ParticipantRecord &&
      reference.path.hashCode == other.reference.path.hashCode;
}

Map<String, dynamic> createParticipantRecordData({
  String? nom,
  String? prenom,
  String? email,
  String? role,
  DateTime? createdAt,
  String? smtpEmail,
  String? smtpAppPassword,
}) {
  final firestoreData = mapToFirestore(
    <String, dynamic>{
      'nom': nom,
      'prenom': prenom,
      'email': email,
      'role': role,
      'created_at': createdAt,
      'smtp_email': smtpEmail,
      'smtp_app_password': smtpAppPassword,
    }.withoutNulls,
  );

  return firestoreData;
}

class ParticipantRecordDocumentEquality implements Equality<ParticipantRecord> {
  const ParticipantRecordDocumentEquality();

  @override
  bool equals(ParticipantRecord? e1, ParticipantRecord? e2) {
    return e1?.nom == e2?.nom &&
        e1?.prenom == e2?.prenom &&
        e1?.email == e2?.email &&
        e1?.role == e2?.role &&
        e1?.createdAt == e2?.createdAt &&
        e1?.smtpEmail == e2?.smtpEmail &&
        e1?.smtpAppPassword == e2?.smtpAppPassword;
  }

  @override
  int hash(ParticipantRecord? e) => const ListEquality().hash([
        e?.nom,
        e?.prenom,
        e?.email,
        e?.role,
        e?.createdAt,
        e?.smtpEmail,
        e?.smtpAppPassword
      ]);

  @override
  bool isValidKey(Object? o) => o is ParticipantRecord;
}
