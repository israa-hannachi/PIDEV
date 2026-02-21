import sys
import json
import math
from collections import defaultdict
from itertools import combinations

def calculate_profile_match(profile, event):
    """
    Factor 1: Profile Match 30%
    Compare user profession, experience level, skills array, and interests array
    with event target audience, required level, tags, and category.
    Returns 0-100 score.
    """
    if not profile or not event:
        return 0

    score = 0
    max_score = 4 

    # 1. Category match (25%)
    category = event.get('category')
    user_cats = profile.get('preferred_categories', {})
    if category in user_cats:
        score += (user_cats[category] / 100.0)

    # 2. Topic/Tag match (25%)
    event_tags = set(event.get('tags', []))
    user_topics = profile.get('preferred_topics', {})
    if event_tags and user_topics:
        overlap = sum((user_topics[t] / 100.0) for t in event_tags if t in user_topics)
        score += min(1.0, overlap)
        
    # 3. Difficulty match (25%)
    event_diff = event.get('difficulty')
    user_diff = profile.get('preferred_difficulty')
    if event_diff and user_diff == event_diff:
        score += 1.0
        
    # 4. Target audience/Profession match (25%)
    user_profession = profile.get('profession', '')
    event_audience = event.get('target_audience', [])
    if user_profession and user_profession in event_audience:
        score += 1.0
        
    return (score / max_score) * 100


def calculate_history_match(history_registrations, event):
    """
    Factor 2: History Match 25%
    Analyze user past registrations. Calculate what percentage fell in same category.
    Check if event is logical follow-up to previously attended event.
    """
    if not history_registrations:
        return 0
        
    score = 0
    max_score = 2
    
    # 1. Category Percentage (50%)
    category = event.get('category')
    cat_match_count = sum(1 for reg in history_registrations if reg.get('category') == category)
    score += (cat_match_count / max(1, len(history_registrations)))
    
    # 2. Sequential/Follow-up (50%)
    previous_event_ids = [reg.get('id') for reg in history_registrations]
    prerequisites = event.get('prerequisites', [])
    
    if prerequisites:
        if any(p in previous_event_ids for p in prerequisites):
            score += 1.0 # Met a prerequisite
    else:
        # Give partial credit if no strict prerequisites exist
        score += 0.5 
        
    return (score / max_score) * 100


def calculate_collaborative_filtering(user_id, all_users_history, candidate_event_id):
    """
    Factor 3: Collaborative Filtering 20%
    Find top 20 most similar users using Jaccard similarity.
    Recommend events those similar users attended.
    """
    if not all_users_history or user_id not in all_users_history:
        return 0
        
    target_user_events = set(all_users_history[user_id])
    if not target_user_events:
        return 0
        
    similarities = {}
    for other_user_id, other_events in all_users_history.items():
        if other_user_id == user_id:
            continue
            
        other_events_set = set(other_events)
        if not other_events_set:
            continue
            
        intersection = len(target_user_events.intersection(other_events_set))
        union = len(target_user_events.union(other_events_set))
        
        jaccard = intersection / union if union > 0 else 0
        similarities[other_user_id] = jaccard
        
    # Top 20 most similar users
    top_similar_users = sorted(similarities.items(), key=lambda x: x[1], reverse=True)[:20]
    
    # Calculate how many of these top users attended the candidate event
    if not top_similar_users:
        return 0
        
    attendee_weight = 0
    total_similarity_weight = sum(score for _, score in top_similar_users)
    
    if total_similarity_weight == 0:
        return 0
        
    for other_user_id, sim_score in top_similar_users:
        if candidate_event_id in all_users_history[other_user_id]:
            attendee_weight += sim_score
            
    return (attendee_weight / total_similarity_weight) * 100


def calculate_popularity_quality(event):
    """
    Factor 4: Popularity & Quality 15%
    Registration fill rate, average rating.
    """
    score = 0
    max_score = 2
    
    # 1. Fill Rate (50%)
    capacity = event.get('capacity', 1)
    registered = event.get('registered', 0)
    
    if capacity > 0:
        fill_rate = registered / capacity
        # We want popular events, but not full ones
        if fill_rate >= 1.0:
            score += 0.2 # Full
        elif fill_rate > 0.8:
            score += 1.0 # Almost full - highly desirable
        else:
            score += fill_rate
            
    # 2. Average Rating (50%)
    rating = event.get('average_rating', 0) # 0-5 scale
    score += (rating / 5.0)
    
    return (score / max_score) * 100


def calculate_timing_availability(event, user_schedule, profile):
    """
    Factor 5: Timing Availability 10%
    Check schedule conflicts (conflict = 0 score).
    """
    # Simplified logic: In a real system we would parse datetimes
    event_start = event.get('start_timestamp')
    event_end = event.get('end_timestamp')
    event_day = event.get('day_of_week')
    
    # 1. Conflict Check (hard constraint)
    for scheduled in user_schedule:
        s_start = scheduled.get('start_timestamp')
        s_end = scheduled.get('end_timestamp')
        
        # Overlap check
        if event_start and event_end and s_start and s_end:
            if max(event_start, s_start) < min(event_end, s_end):
                return 0 # CRITICAL: Conflict exists
                
    # 2. Preferred Day Check
    preferred_days = profile.get('preferred_days', [])
    if event_day and event_day in preferred_days:
        return 100
        
    return 50 # Neutral score if no conflict but not preferred


def main():
    try:
        input_data = sys.stdin.read()
        if not input_data:
            print(json.dumps({"error": "No input provided"}))
            sys.exit(1)

        payload = json.loads(input_data)
        
        target_user_id = payload.get('user_id')
        user_profile = payload.get('profile', {})
        user_history_registrations = payload.get('history', [])
        user_schedule = payload.get('schedule', [])
        all_users_history = payload.get('all_users_history', {}) # {user_id: [event_id1, event_id2]}
        candidate_events = payload.get('candidate_events', [])
        
        registration_count = len(user_history_registrations)
        is_cold_start = registration_count < 3
        
        recommendations = []
        
        for event in candidate_events:
            event_id = event.get('id')
            
            # Feature 6: Cold Start Handler Weights
            if is_cold_start:
                # 60% Profile, 40% Popularity
                w_profile = 0.60
                w_history = 0.0
                w_collab = 0.0
                w_popular = 0.40
                w_timing = 0.0 # Ignore complex timing for now
            else:
                # Standard Weights
                w_profile = 0.30
                w_history = 0.25
                w_collab = 0.20
                w_popular = 0.15
                w_timing = 0.10
                
            # Calculate factors
            s_profile = calculate_profile_match(user_profile, event)
            s_history = calculate_history_match(user_history_registrations, event) if not is_cold_start else 0
            s_collab = calculate_collaborative_filtering(target_user_id, all_users_history, event_id) if not is_cold_start else 0
            s_popular = calculate_popularity_quality(event)
            s_timing = calculate_timing_availability(event, user_schedule, user_profile)
            
            # If timing is 0 due to conflict, skip event entirely
            if not is_cold_start and s_timing == 0:
                continue

            final_score = (
                (s_profile * w_profile) +
                (s_history * w_history) +
                (s_collab * w_collab) +
                (s_popular * w_popular) +
                (s_timing * w_timing)
            )
            
            # Explanations
            explanations = []
            if s_profile > 80:
                explanations.append(f"Correspond parfaitement à votre profil et vos intérêts en {event.get('category')}.")
            if s_history > 80:
                explanations.append("Suite logique aux événements auxquels vous avez déjà participé.")
            if s_collab > 70:
                explanations.append("Des participants ayant le même profil que vous y participent.")
            if s_popular > 90:
                explanations.append("Événement très populaire et fortement recommandé.")
                
            if not explanations:
                explanations.append("Recommandé pour vous.")
                
            recommendations.append({
                "event_id": event_id,
                "score": round(final_score, 2),
                "factors": {
                    "profile": round(s_profile, 2),
                    "history": round(s_history, 2),
                    "collaborative": round(s_collab, 2),
                    "popularity": round(s_popular, 2),
                    "timing": round(s_timing, 2),
                },
                "explanations": explanations
            })
            
        # Sort recommendations by score descending
        recommendations.sort(key=lambda x: x['score'], reverse=True)
        
        print(json.dumps({
            "success": True,
            "is_cold_start": is_cold_start,
            "recommendations": recommendations[:20] # Return top 20 max
        }))
        
    except Exception as e:
        print(json.dumps({"error": str(e), "success": False}))
        sys.exit(1)

if __name__ == "__main__":
    main()
