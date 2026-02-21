import json
import sys
import math
import argparse

def euclidean_distance(v1, v2):
    """Calculates Euclidean distance between two vectors."""
    distance = 0
    # Create a union of all keys from both vectors to handle missing dimensions
    all_keys = set(v1.keys()).union(set(v2.keys()))
    
    for key in all_keys:
        val1 = v1.get(key, 0)
        val2 = v2.get(key, 0)
        distance += (val1 - val2) ** 2
    
    return math.sqrt(distance)

class KNNRecommender:
    def __init__(self, k=5):
        self.k = k
        self.users_data = [] # List of (user_id, feature_vector)

    def fit(self, users_data):
        """
        users_data: list of dicts { 'user_id': int, 'features': { 'cat_1': score, ... } }
        """
        self.users_data = users_data

    def get_neighbors(self, target_features):
        distances = []
        for user in self.users_data:
            dist = euclidean_distance(target_features, user['features'])
            distances.append((user['user_id'], dist))
        
        # Sort by distance (smaller is more similar)
        distances.sort(key=lambda x: x[1])
        return distances[:self.k]

    def recommend_scores(self, target_user_id, events_data):
        """
        Calculates a list of (event_id, score) for a target user based on neighbors.
        events_data: list of dicts { 'event_id': int, 'category': str, 'popularity': float }
        """
        # 1. Find the target user's features
        target_user = next((u for u in self.users_data if u['user_id'] == target_user_id), None)
        if not target_user:
            return [] # Cold start handled in PHP

        # 2. Get neighbors
        neighbors = self.get_neighbors(target_user['features'])
        neighbor_ids = [n[0] for n in neighbors]
        
        # weights = [1/(d+0.01) for _, d in neighbors]
        
        recommendations = []
        for event in events_data:
            # Score based on how many neighbors like this category
            # and adjust by category score of the target user
            base_score = target_user['features'].get(event['category'], 0) * 0.4
            
            # Collaborative signal: shared interests in neighbors
            peer_influence = 0
            for neighbor_id, dist in neighbors:
                neighbor = next(u for u in self.users_data if u['user_id'] == neighbor_id)
                peer_influence += neighbor['features'].get(event['category'], 0) * (1 / (dist + 1))
            
            final_score = base_score + (peer_influence / self.k) * 0.6
            
            # Normalize to 0-100 range roughly
            final_score = min(100, final_score * 5) 
            
            recommendations.append({
                'event_id': event['event_id'],
                'score': round(final_score, 2),
                'reason': f"Based on your interest in {event['category']}."
            })

        return recommendations

def main():
    parser = argparse.ArgumentParser(description='KNN Recommender for Naja7ni')
    parser.add_argument('--target_user', type=int, required=True)
    parser.add_argument('--users_json', type=str, required=True) # JSON string of all user features
    parser.add_argument('--events_json', type=str, required=True) # JSON string of candidate events
    
    args = parser.parse_args()

    try:
        users_data = json.loads(args.users_json)
        events_data = json.loads(args.events_json)
        
        recommender = KNNRecommender(k=min(5, len(users_data)))
        recommender.fit(users_data)
        
        results = recommender.recommend_scores(args.target_user, events_data)
        print(json.dumps(results))
        
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
