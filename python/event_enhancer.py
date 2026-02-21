import json
import sys
import re
import argparse

# Knowledge base for categories and keywords (Bilingual: French & English)
KNOWLEDGE_BASE = {
    'Musique': ['concert', 'festival', 'musique', 'live', 'orchestre', 'chanson', 'pianiste', 'guitare', 'rock', 'jazz', 'music', 'band', 'gig', 'singer'],
    'Sport': ['tournoi', 'match', 'championnat', 'football', 'marathon', 'course', 'yoga', 'fitness', 'entraînement', 'tournament', 'championship', 'workout', 'training', 'race'],
    'Technologie': ['coding', 'hackathon', 'ia', 'web', 'dev', 'intelligence artificielle', 'tech', 'conférence', 'atelier', 'programming', 'artificial intelligence', 'software', 'conference', 'developer'],
    'Formation': ['cours', 'formation', 'webinaire', 'masterclass', 'apprentissage', 'workshop', 'séminaire', 'course', 'training', 'webinar', 'learning', 'seminar'],
    'Networking': ['networking', 'rencontre', 'professionnel', 'conversation', 'connection', 'peers', 'networking event', 'meetup', 'social', 'gathering', 'cocktail', 'peer', 'talk', 'discuss'],
    'Art': ['exposition', 'peinture', 'sculpture', 'galerie', 'vernissage', 'théâtre', 'cinéma', 'exhibition', 'painting', 'gallery', 'theatre', 'movie', 'cinema', 'art'],
    'Gaming': ['esport', 'tournoi', 'jeu', 'console', 'multijoueur', 'streaming', 'gaming', 'e-sport', 'tournament', 'game', 'multiplayer', 'stream']
}

DIFFICULTIES = ['Débutant', 'Intermédiaire', 'Avancé', 'Tous niveaux']

class EventEnhancer:
    def enhance(self, description):
        description_lower = description.lower()
        
        # 1. Category Detection (Weighted by keyword presence)
        suggested_category = "Autre"
        max_matches = 0
        for category, keywords in KNOWLEDGE_BASE.items():
            matches = sum(1 for k in keywords if k in description_lower)
            if matches > max_matches:
                max_matches = matches
                suggested_category = category

        # 2. Difficulty Detection
        suggested_difficulty = "Tous niveaux"
        if any(w in description_lower for w in ['expert', 'avancé', 'poussé', 'difficile', 'advanced', 'expert', 'difficult']):
            suggested_difficulty = "Avancé"
        elif any(w in description_lower for w in ['intermédiaire', 'moyen', 'base', 'intermediate', 'middle', 'moderate']):
            suggested_difficulty = "Intermédiaire"
        elif any(w in description_lower for w in ['débutant', 'initiation', 'découverte', 'facile', 'beginner', 'introduction', 'easy']):
            suggested_difficulty = "Débutant"

        # 3. Best Time Estimation (Logic based on keywords)
        suggested_time = "18:00"
        if any(w in description_lower for w in ['matin', 'petit-déjeuner', 'early', 'morning', 'breakfast']):
            suggested_time = "09:00"
        elif any(w in description_lower for w in ['midi', 'déjeuner', 'lunch', 'noon', 'afternoon']):
            suggested_time = "12:30"
        elif any(w in description_lower for w in ['soir', 'nuit', 'night', 'fête', 'soirée', 'evening', 'cocktail', 'dark', 'dinner']):
            suggested_time = "19:00"

        # 4. Marketing Tags Generation
        tags = []
        if suggested_category != "Autre":
            tags.append(suggested_category)
        if suggested_difficulty != "Tous niveaux":
            tags.append(suggested_difficulty)
        
        # Extract potential topics (Capitalized words or specific patterns)
        topics = re.findall(r'\b[A-Z][a-z]+\b', description)
        tags.extend(topics[:3])

        # 5. Entity Extraction (Price, Capacity, Location)
        
        # Price detection (e.g., "$50", "30€", "20 USD", "Gratuit", "Free")
        suggested_prix = 0
        price_match = re.search(r'(\d+)\s*(€|\$|£|usd|tnd|dt)', description_lower)
        if price_match:
            suggested_prix = int(price_match.group(1))
        elif any(w in description_lower for w in ['gratuit', 'free', '0€', '0$']):
            suggested_prix = 0
            
        # Capacity detection (e.g., "50 personnes", "limit à 30", "max 100")
        suggested_capacite = 50 # Default
        cap_match = re.search(r'(?:limité à|max|maximum|capacité|uniquement)\s*(\d+)', description_lower)
        if not cap_match:
            cap_match = re.search(r'(\d+)\s*(?:personnes|places|invités|people|guests)', description_lower)
        if cap_match:
            suggested_capacite = int(cap_match.group(1))

        # Location extraction (Simple heuristic: phrase after "at", "in", "à", "dans", "lieu:")
        suggested_lieu = ""
        loc_match = re.search(r'(?:at|in|à|dans|lieu[:\s])\s*([A-Z\s][a-zA-Z\s]{3,20})', description)
        if loc_match:
            suggested_lieu = loc_match.group(1).strip()

        # Dates extraction (Very basic: looking for numbers like 20/05 or 20-05)
        # In a real scenario, this would be much more complex
        
        # 6. Smart Marketing Hook
        hooks = {
            'Networking': "Élargissez votre réseau et connectez-vous avec des leaders d'opinion.",
            'Technologie': "Plongez dans le futur de l'innovation technologique.",
            'Formation': "Boostez vos compétences avec nos experts passionnés.",
            'Sport': "Dépassez vos limites lors de ce challenge sportif unique.",
            'Musique': "Vivez une expérience sonore inoubliable en live.",
            'Gaming': "Rejoignez la compétition et montrez votre talent.",
            'Art': "Laissez-vous inspirer par la créativité et la beauté de l'art."
        }
        
        marketing_hook = hooks.get(suggested_category, f"Ne manquez pas cet événement {suggested_category} exceptionnel !")
        
        return {
            'category': suggested_category,
            'difficulty': suggested_difficulty,
            'suggested_time': suggested_time,
            'suggested_prix': suggested_prix,
            'suggested_capacite': suggested_capacite,
            'suggested_lieu': suggested_lieu,
            'tags': list(set(tags)),
            'marketing_hook': marketing_hook
        }

def main():
    parser = argparse.ArgumentParser(description='AI Event Enhancer')
    parser.add_argument('--description', type=str, required=True)
    
    args = parser.parse_args()

    try:
        enhancer = EventEnhancer()
        result = enhancer.enhance(args.description)
        # Ensure the stdout is clean for PHP to parse
        sys.stdout.write(json.dumps(result))
        
    except Exception as e:
        sys.stdout.write(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
