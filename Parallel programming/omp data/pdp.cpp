#include "pdp.hpp"

using namespace std;

// print function
void print_graph(){
    cout << " --- SOLUTION ---" << endl;

    cout << "Verticles" << endl;
    vector<Color> verticesC = solutions[0].vertices;

    int counter = 0;
    for (auto a : verticesC){
        cout << "verticle: " << counter << " Color: " << (a == Green ? "Green" : "Red") << endl;
        counter++;
    }

    cout << endl << "Edges" << endl;

    vector<Edge> edgesC = solutions[0].current;

    for (auto a : edgesC){
        cout << "from " << a.first << " to " << a.second << endl;
    }

    cout << endl;
}

// edge compare
bool compare_edges(Edge& a, Edge& b){
    return a.value > b.value;
}

bool compare_states(State& a, State& b){
	return a.currVal > b.currVal;
}

bool is_valid_graph(vector<Color>& vertices, vector<Edge>& edges){
    for (auto edge : edges){
        Color first = vertices[edge.first];
        Color second = vertices[edge.second];

        if (first != Uncolored && first == second) return false;
    }
    return true;
}

int get_value(vector<Edge>& edges){
    un_s res = 0;
    for (auto edge : edges){
        res += edge.value;
    }
    return res;
}

bool check_existing_solution(vector<Color>& vertices){
    for (unsigned int i = 0; i < solutions.size(); i++){
        vector<Color> verticesC = solutions[i].first;

        if (verticesC == vertices) return true;
    }
    return false;
}

void solve(vector<Color> vertices, vector<Edge> remainingEdges, vector<Edge> currentEdges = {}, 
             un_s currVal = 0, un_s remVal = 0){

    // new max value
    if (currVal >= maxVal){
        #pragma omp critical
        {
            // new max
            if (currVal > maxVal){
                solutions.clear();
                solutions.push_back(State(vertices, remainingEdges, currentEdges, currVal, remVal));
                maxVal = currVal;

            // another solution
            } else if (currVal == maxVal){
                //if (!check_existing_solution(vertices))
                solutions.push_back(State(vertices, remainingEdges, currentEdges, currVal, remVal));
            }
        }
    }

    // ending conditions
    if (remainingEdges.size() == 0 || (currVal + remVal) < maxVal) return;

    // remove first remaining
    Edge temp = remainingEdges.front();

    // if the edge is uncolored from both sides - find next edge with at least one side colored
    if (vertices[temp.first] == Uncolored && vertices[temp.second] == Uncolored){
        int counter = 0;
        for (auto a : remainingEdges){
            if ((vertices[a.first] != Uncolored && vertices[a.second] == Uncolored) || 
            (vertices[a.first] == Uncolored && vertices[a.second] != Uncolored)){
                temp = a;
                remainingEdges.erase(remainingEdges.begin() + counter);
                break;
            }
            counter++;
        }
    } else {
        remainingEdges.erase(remainingEdges.begin());
    }

    // saving used edge
    vector<Edge> newCurrent = currentEdges;

    newCurrent.push_back(temp);

    // new colored vertices
    vector<Color> newVertices = vertices;

    // set colors of edge
    if (vertices[temp.first] == Uncolored){
        newVertices[temp.first] = vertices[temp.second] == Green ? Red : Green;
    } else if (vertices[temp.second] == Uncolored) {
        newVertices[temp.second] = vertices[temp.first] == Green ? Red : Green;
    } 

    // edge used
    if (is_valid_graph(newVertices, newCurrent))
        solve(newVertices, remainingEdges, newCurrent, currVal + temp.value, remVal - temp.value);

    // edge not used
    solve(vertices, remainingEdges, currentEdges, currVal, remVal - temp.value);
}

vector<State> expand_states(vector<State> states, unsigned int limit){
    while (states.size() < limit){
        State tmp = states.front();
        states.erase(states.begin());

        // bad branch
        if ((tmp.currVal + tmp.remVal) < maxVal) continue;

        // new max ?
        if (tmp.currVal > maxVal){
            if (tmp.currVal > maxVal){
                maxVal = tmp.currVal;
            }
        }

        // get new vals
        vector<Edge> newRemaining(tmp.remaining);
        vector<Edge> newCurrent(tmp.current);
        vector<Color> newVertices(tmp.vertices);

        // take first edge
        Edge tmpEdge = newRemaining.front();
        newRemaining.erase(newRemaining.begin());

        newCurrent.push_back(tmpEdge);

        // edge not colored
        if (tmp.vertices[tmpEdge.first] == Uncolored && tmp.vertices[tmpEdge.second] == Uncolored){
            

            newVertices[tmpEdge.first] = Red;
            newVertices[tmpEdge.second] = Green;

            if (is_valid_graph(newVertices, newCurrent)){
                states.push_back(State(newVertices, newRemaining, newCurrent, tmp.currVal + tmpEdge.value, tmp.remVal - tmpEdge.value));
            }

            newVertices[tmpEdge.first] = Green;
            newVertices[tmpEdge.second] = Red;

            if (is_valid_graph(newVertices, newCurrent)){
                states.push_back(State(newVertices, newRemaining, newCurrent, tmp.currVal + tmpEdge.value, tmp.remVal - tmpEdge.value));
            }
        // edge colored from one side    
        } else {
            if (tmp.vertices[tmpEdge.first] == Uncolored){
                newVertices[tmpEdge.first] = tmp.vertices[tmpEdge.second] == Green ? Red : Green;
            } else {
                newVertices[tmpEdge.second] = tmp.vertices[tmpEdge.first] == Green ? Red : Green;
            }

            if (is_valid_graph(newVertices, newCurrent)){
                states.push_back(State(newVertices, newRemaining, newCurrent, tmp.currVal + tmpEdge.value, tmp.remVal - tmpEdge.value));
            }
        }

        // edge not used
        states.push_back(State(tmp.vertices, newRemaining, tmp.current, tmp.currVal, tmp.remVal - tmpEdge.value));
    }
    return states;
}

int main(int argc, char* argv[]){
    vector<Edge> edges;
    un_s graphSize;
    int value = 0;
    string line;
    ifstream file;

    int threads = atoi(argv[1]);

    // read graph size
    file.open(argv[2]);
    getline(file, line);

    graphSize = stoi(line);

    // set vertices
    vector<Color> vertices(graphSize, Uncolored);

    // create edges of vertices with values
    int j = 0;
    string word;
    while (getline(file, line)){
        stringstream ss(line);
        for (int i = 0; i < graphSize; i++){
            ss >> word;
            value = stoi(word);
            if (value > 0 && i > j){
                Edge temp(i, j, value);

                edges.push_back(temp);
            }
        }
        j++;
    }

    file.close();

    // sort by edge value
    std::sort(edges.begin(), edges.end(), compare_edges);

    // colour first vertice
    vertices[edges.front().first] = Green;

    // remaining value
    unsigned int remVal = get_value(edges);

    // create stats
    vector<State> states;
    states.push_back(State(vertices, edges, {}, 0, remVal));

    vector<State> expanded = expand_states(states, 100);
	
    // sort again
	std::sort(expanded.begin(), expanded.end(), compare_states);

    auto start = chrono::high_resolution_clock::now();

    // solve
    #pragma omp parallel for schedule(dynamic) num_threads(threads)
    for (unsigned int i = 0; i < expanded.size(); i++){
        State tmp = expanded[i];
        solve(tmp.vertices, tmp.remaining, tmp.current, tmp.currVal, tmp.remVal);
    }

    auto stop = chrono::high_resolution_clock::now();

    print_graph();

    cout << "maxVal: " << maxVal << " solutions: " << solutions.size() << endl;
    cout << "time: " << chrono::duration_cast<chrono::milliseconds>(stop - start).count() << " ms" << endl;
    return 0;
}
