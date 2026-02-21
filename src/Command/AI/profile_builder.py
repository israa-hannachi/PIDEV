import sys
import json
import math
from collections import defaultdict

def main():
    try:
        # Read JSON payload from stdin
        input_data = sys.stdin.read()
        if not input_data:
            print(json.dumps({"error": "No input provided"}))
            sys.exit(1)

        payload = json.loads(input_data)
        interactions = payload.get('interactions', [])
        
        # Calculate scores
        category_scores = defaultdict(float)
        topic_scores = defaultdict(float)
        difficulty_scores = defaultdict(float)
        total_activity_score = 0.0
        
        # Weigh interactions differently
        weights = {
            'attend': 5.0,
            'register': 4.0,
            'favorite': 3.0,
            'view': 1.0,
            'cancel': -2.0,
            'not_interested': -5.0
        }
        
        for interaction in interactions:
            event = interaction.get('event', {})
            itype = interaction.get('type')
            
            weight = weights.get(itype, 0.0)
            
            # Boost weight if view duration is long (>60s)
            if itype == 'view' and interaction.get('duration', 0) > 60:
                weight = 1.5
                
            total_activity_score += max(0, weight) # Don't decrease overall activity for negative actions, just category prefs
            
            category = event.get('category')
            if category:
                category_scores[category] += weight

            # Assuming topic and difficulty might be passed in tags/metadata in the future
            tags = event.get('tags', [])
            for tag in tags:
                topic_scores[tag] += weight
                
            difficulty = event.get('difficulty')
            if difficulty:
                difficulty_scores[difficulty] += weight

        # Normalize and filter negative scores
        def normalize_top(scores_dict, limit=5):
            positive_scores = {k: v for k, v in scores_dict.items() if v > 0}
            sorted_items = sorted(positive_scores.items(), key=lambda x: x[1], reverse=True)[:limit]
            
            if not sorted_items:
                return {}
                
            max_val = sorted_items[0][1]
            return {k: round((v / max_val) * 100, 2) for k, v in sorted_items}

        top_categories = normalize_top(category_scores, 3)
        top_topics = normalize_top(topic_scores, 5)
        
        best_difficulty = None
        if difficulty_scores:
            best_difficulty = max(difficulty_scores.items(), key=lambda x: x[1])[0]

        # Calculate completeness (0-100) based on how much data we have
        completeness = min(100.0, len(interactions) * 5.0)

        result = {
            "success": True,
            "profile": {
                "preferred_categories": top_categories,
                "preferred_topics": top_topics,
                "preferred_difficulty": best_difficulty,
                "activity_score": round(total_activity_score, 2),
                "profile_completeness": round(completeness, 2)
            }
        }
        
        print(json.dumps(result))
        
    except Exception as e:
        print(json.dumps({"error": str(e), "success": False}))
        sys.exit(1)

if __name__ == "__main__":
    main()
