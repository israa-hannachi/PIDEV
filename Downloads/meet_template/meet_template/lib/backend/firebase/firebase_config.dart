import 'package:firebase_core/firebase_core.dart';
import 'package:flutter/foundation.dart';

Future initFirebase() async {
  if (kIsWeb) {
    await Firebase.initializeApp(
        options: FirebaseOptions(
            apiKey: "AIzaSyD1CXklqFTJi4y4R5jRjCGiQ2BRozLBwoc",
            authDomain: "meet-template-x3fql1.firebaseapp.com",
            projectId: "meet-template-x3fql1",
            storageBucket: "meet-template-x3fql1.firebasestorage.app",
            messagingSenderId: "219861818507",
            appId: "1:219861818507:web:557a75f235b60b9aec088e"));
  } else {
    await Firebase.initializeApp();
  }
}
