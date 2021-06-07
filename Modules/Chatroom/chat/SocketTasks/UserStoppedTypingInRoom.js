const Container = require('../AppContainer'),
    UserStoppedTyping = require('../Model/Messages/UserStoppedTyping');

module.exports = function (roomId, subRoomId) {
    const serverRoomId = Container.createServerRoomId(roomId, subRoomId),
        namespace = Container.getNamespace(this.nsp.name),
        room = namespace.getRoom(serverRoomId);

    Container.getLogger().info('UserStoppedTypingInRoom command send to room %s of namespace %s', serverRoomId, namespace.getName());
    if (typeof this.subscriber === "undefined") {
        Container.getLogger().error("Missing subscriber, don't process message");
        return;
    }

    if (!room.hasSubscriber(this.subscriber.getId())) {
        Container.getLogger().error("Subscriber is not in room, don't process message");
        return;
    }

    Container.getLogger().debug("Subscribed with id %s stopped typing", this.subscriber.getId());

    namespace.getIO().to(serverRoomId).emit(
        'userStoppedTyping',
        UserStoppedTyping.create(roomId, subRoomId, this.subscriber.getId())
    );
};
