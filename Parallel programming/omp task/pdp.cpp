#include "pdp.hpp"

using namespace std;

// print function
void print_graph(){
    cout << " --- SOLUTION ---" << endl;

    cout << "Verticles" << endl;
    vector<Colour> verticesC = solutions[0].first;

    int counter = 0;
    for (auto a : verticesC){
        cout << "verticle: " << counter << " colour: " << (a == Green ? "Green" : "Red") << endl;
        counter++;
    }

    cout << endl << "Edges" << endl;

    vector<Edge> edgesC = solutions[0].second;

    for (auto a : edgesC){
        cout << "from " << a.first << " to " << a.second << endl;
    }

    cout << endl;
}

// edge compare
bool compare_edges(Edge& a, Edge& b){
    return a.value > b.value;
}

// check parity
bool is_valid_graph(vector<Colour>& vertices, vector<Edge>& edges){
    for (auto edge : edges){
        Colour first = vertices[edge.first];
        Colour second = vertices[edge.second];

        if (first != Uncolored && first == second) return false;
    }
    return true;
}

// calculate vector max value
int get_value(vector<Edge>& edges){
    un_s res = 0;
    for (auto edge : edges){
        res += edge.value;
    }
    return res;
}

// check if the solution exists
bool check_existing_solution(vector<Colour>& vertices){
    for (int i = 0; i < int(solutions.size()); i++){
        vector<Colour> verticesC = solutions[i].first;

        if (verticesC == vertices) return true;
    }
    return false;
}

// main function
void solve(vector<Colour> vertices, vector<Edge> remainingEdges, vector<Edge> currentEdges = {}, 
            un_s currVal = 0, un_s remVal = 0, unsigned long int depth = 0){

    // depth counter
    if (depth < 8)
        depth++;

    // new max value
    if (currVal >= maxVal){
        #pragma omp critical
        {
            // new max
            if (currVal > maxVal){
                solutions.clear();
                solutions.push_back(make_pair(vertices, currentEdges));
                maxVal = currVal;

            // another solution
            } else if (currVal == maxVal){
                //if (!check_existing_solution(vertices))
                solutions.push_back(make_pair(vertices, currentEdges));
            }
        }
    }

    // ending conditions
    if (remainingEdges.size() == 0 || (currVal + remVal) < maxVal) return;

    // remove first remaining
    Edge temp = remainingEdges.front();

    // if the edge is uncolored from both sides - find next edge with at least one colored
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

    // new current edges
    vector<Edge> newCurrent(currentEdges);

    // push back edge
    newCurrent.push_back(temp);

    // new coloured vertices
    vector<Colour> newVertices(vertices);

    if (vertices[temp.first] == Uncolored){
        newVertices[temp.first] = vertices[temp.second] == Green ? Red : Green;
    } else if (vertices[temp.second] == Uncolored) {
        newVertices[temp.second] = vertices[temp.first] == Green ? Red : Green;
    } 

    #pragma omp task if (depth < 8)
    {
        // edge used
        if (is_valid_graph(newVertices, newCurrent))
            solve(newVertices, remainingEdges, newCurrent, currVal + temp.value, remVal - temp.value, depth);
    }
    
    #pragma omp task if (depth < 8)
    {
        // edge not used
        solve(vertices, remainingEdges, currentEdges, currVal, remVal - temp.value, depth);
    }
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
    vector<Colour> vertices(graphSize, Uncolored);

    int j = 0;
    string word;
    while (getline(file, line)){
        stringstream ss(line);
        for (int i = 0; i < graphSize; i++){
            ss >> word;
            value = stoi(word);
            if (value > 0 && i > j){
                Edge temp;
                temp.first = i;
                temp.second = j;
                temp.value = value;

                edges.push_back(temp);
            }
        }
        j++;
    }

    // descending sort
    std::sort(edges.begin(), edges.end(), compare_edges);

    // colour first vertices
    vertices[edges.front().first] = Green;

    // calculate remaining value of all edges
    unsigned int remVal = get_value(edges);

    // start time
    auto start = chrono::high_resolution_clock::now();

    #pragma omp parallel num_threads(threads)
    {
        #pragma omp single
        {
            cout << "num of threds: " << omp_get_num_threads() << endl;

            solve(vertices, edges, {}, 0, remVal, 0);
        }
    }

    // stop time
    auto stop = chrono::high_resolution_clock::now();

    // print solution
    print_graph();

    // print maximum value, # of solutions and time it took
    cout << "max: " << maxVal << " solutions: " << solutions.size() << endl;
    cout << "time: " << chrono::duration_cast<chrono::milliseconds>(stop - start).count() << " ms"<< endl;

    return 0;
}