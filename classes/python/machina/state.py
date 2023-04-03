class State:

    def __init__(self, label):
        self.label = label

    def __str__(self):
        return self.label

    def __hash__(self):
        return hash(self.label)

    def __eq__(self, other):
        return self.label == other.label


if __name__ == '__main__':
    s = set()
    s.add(State('q0'))
    print(s.pop())
